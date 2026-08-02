import { usePage } from '@inertiajs/react';
import AppLogoIcon from './app-logo-icon';

export default function AppLogo() {
    const { props } = usePage();
    const name = props.name as string;
    const globalLogo = props.logo as string | null;
    const tenantLogo = props.tenantLogo as string | null;

    // Tenant logo takes priority, then global logo, then default icon
    const displayLogo = tenantLogo || globalLogo;

    return (
        <div className="flex items-center gap-2.5">
            {displayLogo ? (
                <div className="flex size-9 items-center justify-center">
                    <img
                        src={displayLogo}
                        alt={name}
                        className="max-h-9 max-w-9 object-contain"
                    />
                </div>
            ) : (
                <div className="flex size-9 items-center justify-center">
                    <AppLogoIcon className="size-9" />
                </div>
            )}
            <span className="truncate text-sm font-semibold leading-none whitespace-nowrap">
                {name}
            </span>
        </div>
    );
}
