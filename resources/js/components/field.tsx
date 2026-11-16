import InputError from '@/components/input-error';
import { Label } from '@/components/ui/label';
import type { ReactNode } from 'react';

/** Etiqueta, control y error de un campo del formulario. */
export function Field({
    label,
    htmlFor,
    error,
    required = false,
    hint,
    className,
    children,
}: {
    label: string;
    htmlFor?: string;
    error?: string;
    required?: boolean;
    hint?: string;
    className?: string;
    children: ReactNode;
}) {
    return (
        <div className={className}>
            <Label htmlFor={htmlFor} className="mb-1.5 block">
                {label}
                {required && <span className="ml-0.5 text-destructive">*</span>}
            </Label>
            {children}
            {hint && <p className="mt-1 text-xs text-muted-foreground">{hint}</p>}
            <InputError message={error} className="mt-1" />
        </div>
    );
}
