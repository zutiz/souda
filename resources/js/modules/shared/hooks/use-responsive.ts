import { useState, useEffect, useCallback } from 'react';

type Breakpoint = 'sm' | 'md' | 'lg' | 'xl' | '2xl';

const breakpoints: Record<Breakpoint, number> = {
    sm: 640,
    md: 768,
    lg: 1024,
    xl: 1280,
    '2xl': 1536,
};

export function useBreakpoint(breakpoint: Breakpoint): boolean {
    const [isAbove, setIsAbove] = useState(false);

    useEffect(() => {
        const check = () => {
            setIsAbove(window.innerWidth >= breakpoints[breakpoint]);
        };

        check();
        window.addEventListener('resize', check);
        return () => window.removeEventListener('resize', check);
    }, [breakpoint]);

    return isAbove;
}

export function useIsMobile(): boolean {
    return !useBreakpoint('md');
}

export function useIsTablet(): boolean {
    const [isTablet, setIsTablet] = useState(false);

    useEffect(() => {
        const check = () => {
            setIsTablet(window.innerWidth >= breakpoints.md && window.innerWidth < breakpoints.lg);
        };

        check();
        window.addEventListener('resize', check);
        return () => window.removeEventListener('resize', check);
    }, []);

    return isTablet;
}

// Screen size hook with debouncing
export function useScreenSize() {
    const [size, setSize] = useState<{
        width: number;
        height: number;
        isMobile: boolean;
        isTablet: boolean;
        isDesktop: boolean;
    }>({
        width: typeof window !== 'undefined' ? window.innerWidth : 0,
        height: typeof window !== 'undefined' ? window.innerHeight : 0,
        isMobile: true,
        isTablet: false,
        isDesktop: false,
    });

    useEffect(() => {
        let timeout: NodeJS.Timeout;

        const handleResize = () => {
            clearTimeout(timeout);
            timeout = setTimeout(() => {
                const width = window.innerWidth;
                const height = window.innerHeight;
                setSize({
                    width,
                    height,
                    isMobile: width < breakpoints.md,
                    isTablet: width >= breakpoints.md && width < breakpoints.lg,
                    isDesktop: width >= breakpoints.lg,
                });
            }, 100);
        };

        handleResize();
        window.addEventListener('resize', handleResize);
        return () => {
            window.removeEventListener('resize', handleResize);
            clearTimeout(timeout);
        };
    }, []);

    return size;
}

// Orientation hook for mobile devices
export function useOrientation() {
    const [orientation, setOrientation] = useState<'portrait' | 'landscape'>('portrait');

    useEffect(() => {
        const handleChange = () => {
            setOrientation(window.innerHeight > window.innerWidth ? 'portrait' : 'landscape');
        };

        handleChange();
        window.addEventListener('resize', handleChange);
        return () => window.removeEventListener('resize', handleChange);
    }, []);

    return orientation;
}

// Touch detection hook
export function useIsTouchDevice(): boolean {
    const [isTouch, setIsTouch] = useState(false);

    useEffect(() => {
        setIsTouch(
            'ontouchstart' in window ||
            navigator.maxTouchPoints > 0 ||
            window.matchMedia('(pointer: coarse)').matches
        );
    }, []);

    return isTouch;
}

// Reduced motion hook for accessibility
export function usePrefersReducedMotion(): boolean {
    const [prefersReduced, setPrefersReduced] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia('(prefers-reduced-motion: reduce)');
        setPrefersReduced(mediaQuery.matches);

        const handleChange = (e: MediaQueryListEvent) => {
            setPrefersReduced(e.matches);
        };

        mediaQuery.addEventListener('change', handleChange);
        return () => mediaQuery.removeEventListener('change', handleChange);
    }, []);

    return prefersReduced;
}

// Media query hook
export function useMediaQuery(query: string): boolean {
    const [matches, setMatches] = useState(false);

    useEffect(() => {
        const mediaQuery = window.matchMedia(query);
        setMatches(mediaQuery.matches);

        const handleChange = (e: MediaQueryListEvent) => {
            setMatches(e.matches);
        };

        mediaQuery.addEventListener('change', handleChange);
        return () => mediaQuery.removeEventListener('change', handleChange);
    }, [query]);

    return matches;
}

// Color scheme detection (light/dark mode)
export function useColorScheme(): 'light' | 'dark' | 'no-preference' {
    const prefersDark = useMediaQuery('(prefers-color-scheme: dark)');

    if (typeof window === 'undefined') return 'no-preference';
    if (document.documentElement.classList.contains('dark')) return 'dark';
    if (prefersDark) return 'dark';
    return 'light';
}

// Keyboard navigation detection
export function useIsKeyboardUser(): boolean {
    const [isKeyboard, setIsKeyboard] = useState(false);

    useEffect(() => {
        let last = 0;

        const handleKeyDown = (e: KeyboardEvent) => {
            if (e.key === 'Tab') {
                const now = Date.now();
                if (now - last > 100) {
                    setIsKeyboard(true);
                }
                last = now;
            }
        };

        const handleMouseDown = () => {
            setIsKeyboard(false);
        };

        window.addEventListener('keydown', handleKeyDown);
        window.addEventListener('mousedown', handleMouseDown);

        return () => {
            window.removeEventListener('keydown', handleKeyDown);
            window.removeEventListener('mousedown', handleMouseDown);
        };
    }, []);

    return isKeyboard;
}

// Safe area insets for notched devices
export function useSafeAreaInsets() {
    const [insets, setInsets] = useState({
        top: 0,
        right: 0,
        bottom: 0,
        left: 0,
    });

    useEffect(() => {
        const computed = getComputedStyle(document.documentElement);
        setInsets({
            top: parseInt(computed.getPropertyValue('--sat') || '0'),
            right: parseInt(computed.getPropertyValue('--sar') || '0'),
            bottom: parseInt(computed.getPropertyValue('--sab') || '0'),
            left: parseInt(computed.getPropertyValue('--sal') || '0'),
        });
    }, []);

    return insets;
}