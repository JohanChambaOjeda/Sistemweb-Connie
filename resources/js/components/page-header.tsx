import type { ReactNode } from 'react';

/** Encabezado de pantalla: título, explicación corta y acciones a la derecha. */
export function PageHeader({ title, description, children }: { title: string; description?: string; children?: ReactNode }) {
    return (
        <div className="flex flex-wrap items-end justify-between gap-3">
            <div>
                <h1 className="text-xl font-semibold tracking-tight">{title}</h1>
                {description && <p className="mt-1 text-sm text-muted-foreground">{description}</p>}
            </div>
            {children && <div className="flex items-center gap-2">{children}</div>}
        </div>
    );
}
