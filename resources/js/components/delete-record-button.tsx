import { Button } from '@/components/ui/button';
import {
    Dialog,
    DialogClose,
    DialogContent,
    DialogDescription,
    DialogFooter,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/components/ui/dialog';
import { useTranslations } from '@/lib/i18n';
import { router } from '@inertiajs/react';
import { Trash2 } from 'lucide-react';
import { useState } from 'react';

/** Botón de eliminar con confirmación, para no borrar por accidente. */
export function DeleteRecordButton({ url, name, label }: { url: string; name: string; label?: string }) {
    const { t } = useTranslations();
    const [open, setOpen] = useState(false);

    const destroy = () => {
        router.delete(url, {
            preserveScroll: true,
            onFinish: () => setOpen(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={setOpen}>
            <DialogTrigger asChild>
                <Button variant="ghost" size="sm" className="text-destructive hover:text-destructive">
                    <Trash2 className="h-4 w-4" />
                    {label && <span className="ml-1">{label}</span>}
                </Button>
            </DialogTrigger>
            <DialogContent>
                <DialogHeader>
                    <DialogTitle>{t('common.actions.delete')}</DialogTitle>
                    <DialogDescription>{t('common.confirm_delete', { name })}</DialogDescription>
                </DialogHeader>
                <DialogFooter>
                    <DialogClose asChild>
                        <Button variant="outline">{t('common.actions.cancel')}</Button>
                    </DialogClose>
                    <Button variant="destructive" onClick={destroy}>
                        {t('common.actions.delete')}
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
}
