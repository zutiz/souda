'use client';

import { router, usePage } from '@inertiajs/react';
import { useState } from 'react';
import { HexColorPicker } from 'react-colorful';
import { useToast } from '@/modules/shared/hooks/use-toast';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Input } from '@/components/ui/input';
import { Popover, PopoverContent, PopoverTrigger } from '@/components/ui/popover';
import { cn } from '@/lib/utils';

function hexToRgb(hex: string): { r: number; g: number; b: number } | null {
    const result = /^#?([a-f\d]{2})([a-f\d]{2})([a-f\d]{2})$/i.exec(hex);
    return result ? { r: parseInt(result[1], 16), g: parseInt(result[2], 16), b: parseInt(result[3], 16) } : null;
}

function getLuminance(hex: string): number {
    const rgb = hexToRgb(hex);
    if (!rgb) return 0;
    // sRGB relative luminance
    const [r, g, b] = [rgb.r, rgb.g, rgb.b].map((c) => {
        c /= 255;
        return c <= 0.03928 ? c / 12.92 : Math.pow((c + 0.055) / 1.055, 2.4);
    });
    return 0.2126 * r + 0.7152 * g + 0.0722 * b;
}

function getBrandingStyleElement(): HTMLStyleElement {
    let el = document.getElementById('tenant-branding-vars') as HTMLStyleElement | null;
    if (!el) {
        el = document.createElement('style');
        el.id = 'tenant-branding-vars';
        document.head.appendChild(el);
    }
    return el;
}

function applyBrandingLocally(overrides: { primary?: string; accent?: string }) {
    const el = getBrandingStyleElement();
    const surfaceBlock = el.textContent?.match(/:root:not\(\.dark\)\s*\{[^}]*\}/)?.[0] ?? '';
    const brandBlock = el.textContent?.match(/:root\s*\{[^}]*\}/)?.[0] ?? '';
    const vars = new Map<string, string>();

    // Parse existing brand vars from the :root block
    const brandContent = brandBlock.replace(/^[^{]*\{/, '').replace(/\}$/, '').trim();

    for (const part of brandContent.split(';')) {
        const trimmed = part.trim();
        if (!trimmed) continue;
        const colonIdx = trimmed.indexOf(':');
        if (colonIdx === -1) continue;
        vars.set(trimmed.slice(0, colonIdx).trim(), trimmed.slice(colonIdx + 1).trim());
    }

    if (overrides.primary) {
        vars.set('--primary', overrides.primary);
        const fg = getLuminance(overrides.primary) > 0.4 ? 'oklch(0.205 0 0)' : 'oklch(0.985 0 0)';
        vars.set('--primary-foreground', fg);
    }

    if (overrides.accent) {
        vars.set('--accent', overrides.accent);
        const fg = getLuminance(overrides.accent) > 0.4 ? 'oklch(0.205 0 0)' : 'oklch(0.985 0 0)';
        vars.set('--accent-foreground', fg);
    }

    const cssVars = Array.from(vars.entries()).map(([k, v]) => `${k}: ${v};`);
    const brandBlockOut = `:root { ${cssVars.join(' ')} }`;

    el.textContent = [brandBlockOut, surfaceBlock].filter(Boolean).join('\n');
}

function clearBrandingLocally() {
    const el = document.getElementById('tenant-branding-vars') as HTMLStyleElement | null;
    if (el) el.textContent = '';
}

export function BrandColorsSettings() {
    const { props } = usePage<{
        currentTenant?: { id: string; name: string };
        tenant_config?: { branding?: Record<string, string> };
    }>();
    const { toast } = useToast();

    const currentBranding = props.tenant_config?.branding ?? {};
    const currentTenant = props.currentTenant;

    const [primaryColor, setPrimaryColor] = useState(
        currentBranding.primary?.startsWith('#')
            ? currentBranding.primary
            : ''
    );
    const [accentColor, setAccentColor] = useState(
        currentBranding.accent?.startsWith('#')
            ? currentBranding.accent
            : ''
    );
    const [logoUrl, setLogoUrl] = useState(currentBranding.logo_url ?? '');
    const [isSubmitting, setIsSubmitting] = useState(false);

    const handleSubmit = async () => {
        setIsSubmitting(true);

        const formData = new FormData();

        // Only send non-empty values
        if (primaryColor) {
            formData.append('brand_primary_color', primaryColor);
        }

        if (accentColor) {
            formData.append('brand_accent_color', accentColor);
        }

        if (logoUrl) {
            formData.append('brand_logo_url', logoUrl);
        }

        // If both colors are cleared, send reset signal
        if (!primaryColor && !accentColor && !currentBranding.primary && !currentBranding.accent) {
            // No change
        }

        try {
            const response = await fetch('/settings/branding', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                },
                body: formData,
            });

            if (response.ok || response.redirected) {
                applyBrandingLocally({
                    ...(primaryColor ? { primary: primaryColor } : {}),
                    ...(accentColor ? { accent: accentColor } : {}),
                });

                toast({
                    title: 'Branding updated',
                    description: 'Your brand colors and logo have been saved.',
                });

                router.reload();
            } else {
                throw new Error('Failed to save');
            }
        } catch {
            toast({
                title: 'Error',
                description: 'Failed to save branding settings. Please try again.',
                variant: 'destructive',
            });
        } finally {
            setIsSubmitting(false);
        }
    };

    const handleResetColors = async () => {
        setPrimaryColor('');
        setAccentColor('');

        const formData = new FormData();
        formData.append('reset_colors', '1');

        try {
            const response = await fetch('/settings/branding', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                },
                body: formData,
            });

            if (response.ok || response.redirected) {
                clearBrandingLocally();

                toast({
                    title: 'Colors reset',
                    description: 'Brand colors have been reset to industry default.',
                });

                router.reload();
            }
        } catch {
            toast({
                title: 'Error',
                description: 'Failed to reset colors.',
                variant: 'destructive',
            });
        }
    };

    const handleResetLogo = async () => {
        setLogoUrl('');

        const formData = new FormData();
        formData.append('reset_logo', '1');

        try {
            const response = await fetch('/settings/branding', {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': (document.querySelector('meta[name="csrf-token"]') as HTMLMetaElement)?.content ?? '',
                },
                body: formData,
            });

            if (response.ok || response.redirected) {
                toast({
                    title: 'Logo reset',
                    description: 'Custom logo URL has been removed.',
                });

                router.reload();
            }
        } catch {
            toast({
                title: 'Error',
                description: 'Failed to reset logo.',
                variant: 'destructive',
            });
        }
    };

    return (
        <Card>
            <CardHeader>
                <CardTitle>Brand Colors</CardTitle>
                <CardDescription>
                    Customize your brand's primary and accent colors. These override your industry default theme.
                </CardDescription>
            </CardHeader>
            <CardContent className="space-y-6">
                <div className="grid gap-6 sm:grid-cols-2">
                    {/* Primary Color */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Primary Color</label>
                        <div className="flex items-center gap-3">
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button
                                        type="button"
                                        className={cn(
                                            'h-10 w-10 rounded-lg border-2 border-border cursor-pointer transition-transform hover:scale-105',
                                            !primaryColor && 'bg-gradient-to-br from-neutral-400 to-neutral-600'
                                        )}
                                        style={primaryColor ? { backgroundColor: primaryColor } : undefined}
                                        title={primaryColor || 'Click to set color'}
                                    />
                                </PopoverTrigger>
                                <PopoverContent className="w-auto p-3" align="start">
                                    <HexColorPicker color={primaryColor || '#000000'} onChange={setPrimaryColor} />
                                    <div className="mt-2">
                                        <Input
                                            value={primaryColor}
                                            onChange={(e) => {
                                                const val = e.target.value;
                                                if (/^#[0-9A-Fa-f]{0,6}$/.test(val)) {
                                                    setPrimaryColor(val.toUpperCase());
                                                }
                                            }}
                                            placeholder="#000000"
                                            className="h-9 font-mono text-sm"
                                            maxLength={7}
                                        />
                                    </div>
                                </PopoverContent>
                            </Popover>
                            <span className="text-sm text-muted-foreground font-mono">
                                {primaryColor || currentBranding.primary || 'Industry default'}
                            </span>
                        </div>
                    </div>

                    {/* Accent Color */}
                    <div className="space-y-2">
                        <label className="text-sm font-medium">Accent Color</label>
                        <div className="flex items-center gap-3">
                            <Popover>
                                <PopoverTrigger asChild>
                                    <button
                                        type="button"
                                        className={cn(
                                            'h-10 w-10 rounded-lg border-2 border-border cursor-pointer transition-transform hover:scale-105',
                                            !accentColor && 'bg-gradient-to-br from-neutral-300 to-neutral-500'
                                        )}
                                        style={accentColor ? { backgroundColor: accentColor } : undefined}
                                        title={accentColor || 'Click to set color'}
                                    />
                                </PopoverTrigger>
                                <PopoverContent className="w-auto p-3" align="start">
                                    <HexColorPicker color={accentColor || '#000000'} onChange={setAccentColor} />
                                    <div className="mt-2">
                                        <Input
                                            value={accentColor}
                                            onChange={(e) => {
                                                const val = e.target.value;
                                                if (/^#[0-9A-Fa-f]{0,6}$/.test(val)) {
                                                    setAccentColor(val.toUpperCase());
                                                }
                                            }}
                                            placeholder="#000000"
                                            className="h-9 font-mono text-sm"
                                            maxLength={7}
                                        />
                                    </div>
                                </PopoverContent>
                            </Popover>
                            <span className="text-sm text-muted-foreground font-mono">
                                {accentColor || currentBranding.accent || 'Industry default'}
                            </span>
                        </div>
                    </div>
                </div>

                {/* Preview */}
                <div className="rounded-lg border p-4 space-y-3">
                    <p className="text-sm font-medium text-muted-foreground">Preview</p>
                    <div className="flex items-center gap-3">
                        <div
                            className="h-12 w-12 rounded-lg border"
                            style={{ backgroundColor: primaryColor || currentBranding.primary || '#3B82F6' }}
                        />
                        <div
                            className="h-12 w-12 rounded-lg border"
                            style={{ backgroundColor: accentColor || currentBranding.accent || '#E5E7EB' }}
                        />
                        <span className="text-sm text-muted-foreground">
                            Primary / Accent
                        </span>
                    </div>
                </div>

                {/* Custom Logo URL */}
                <div className="space-y-2">
                    <label className="text-sm font-medium">Custom Logo URL</label>
                    <div className="flex items-center gap-2">
                        <Input
                            value={logoUrl}
                            onChange={(e) => setLogoUrl(e.target.value)}
                            placeholder="https://example.com/logo.png"
                            className="font-mono text-sm"
                        />
                        {currentBranding.logo_url && (
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={handleResetLogo}
                            >
                                Reset
                            </Button>
                        )}
                    </div>
                    <p className="text-xs text-muted-foreground">
                        Leave empty to use your industry default logo. Overrides tenant logo.
                    </p>
                </div>

                {/* Actions */}
                <div className="flex items-center gap-3 pt-2">
                    <Button onClick={handleSubmit} disabled={isSubmitting}>
                        {isSubmitting ? 'Saving...' : 'Save Branding'}
                    </Button>
                    {(primaryColor || accentColor || currentBranding.primary || currentBranding.accent) && (
                        <Button type="button" variant="outline" onClick={handleResetColors}>
                            Reset to Industry Default
                        </Button>
                    )}
                </div>
            </CardContent>
        </Card>
    );
}