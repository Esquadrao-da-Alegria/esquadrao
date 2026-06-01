import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/components/ui/dialog';
import {
    painelInputClass,
    painelLabelClass,
} from '@/lib/painelFormFieldClasses';
import { InertiaFormProps } from '@inertiajs/react';
import { MailPlus } from 'lucide-react';

interface ConviteForm {
    name: string;
    email: string;
}

interface Props {
    aberto: boolean;
    form: InertiaFormProps<ConviteForm>;
    onOpenChange: (aberto: boolean) => void;
    onSubmit: () => void;
}

const ConviteModal: React.FC<Props> = ({
    aberto,
    form,
    onOpenChange,
    onSubmit,
}) => {
    const { data, setData, processing, errors } = form;

    return (
        <Dialog open={aberto} onOpenChange={onOpenChange}>
            <DialogContent className="border-amber-100 bg-white">
                <DialogHeader>
                    <DialogTitle className="text-amber-950">
                        Convidar voluntário
                    </DialogTitle>
                </DialogHeader>
                <form
                    id="convite-voluntario-form"
                    className="space-y-5"
                    onSubmit={(event) => {
                        event.preventDefault();
                        onSubmit();
                    }}
                >
                    <div>
                        <label
                            htmlFor="convite_name"
                            className={painelLabelClass}
                        >
                            Nome completo *
                        </label>
                        <input
                            id="convite_name"
                            type="text"
                            value={data.name}
                            onChange={(event) =>
                                setData('name', event.target.value)
                            }
                            className={painelInputClass}
                            autoComplete="name"
                            required
                        />
                        {errors.name ? (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.name}
                            </p>
                        ) : null}
                    </div>
                    <div>
                        <label
                            htmlFor="convite_email"
                            className={painelLabelClass}
                        >
                            E-mail *
                        </label>
                        <input
                            id="convite_email"
                            type="email"
                            value={data.email}
                            onChange={(event) =>
                                setData('email', event.target.value)
                            }
                            className={painelInputClass}
                            autoComplete="email"
                            required
                        />
                        {errors.email ? (
                            <p className="mt-1 text-sm text-red-600">
                                {errors.email}
                            </p>
                        ) : null}
                    </div>
                </form>
                <DialogFooter>
                    <button
                        type="button"
                        onClick={() => onOpenChange(false)}
                        className="inline-flex items-center justify-center rounded-full border px-5 py-3 font-semibold text-gray-700 transition hover:bg-gray-50"
                    >
                        Cancelar
                    </button>
                    <button
                        type="submit"
                        form="convite-voluntario-form"
                        disabled={processing}
                        className="inline-flex items-center justify-center gap-2 rounded-full border-2 border-amber-600 bg-white px-6 py-3 font-semibold text-amber-700 transition hover:bg-amber-50 disabled:opacity-70"
                    >
                        <MailPlus className="size-4" aria-hidden />
                        {processing ? 'Enviando...' : 'Convidar voluntário'}
                    </button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    );
};

export default ConviteModal;
