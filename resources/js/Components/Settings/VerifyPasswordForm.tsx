import {useForm} from '@inertiajs/react';
import { useEffect } from 'react';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import FieldGroup from '@/Components/Form/FieldGroup';

export default function VerifyPasswordForm({phone} : {phone: string}) {
    const { data, setData, errors, post, processing } = useForm({
        password: "",
        phone: ""
    });

    useEffect(() => {
        if (phone) {
            setData('phone', phone);
        }
    }, [phone]);

    function handleSubmitVerify(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(route('settings.verify-password'));
    }

    return (
                <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
                <form onSubmit={handleSubmitVerify}>
                    <div className="grid gap-8 p-8 lg:grid-cols-2">
                    <FieldGroup
                        label="Пароль"
                        name="password"
                        error={errors.password}
                    >
                        <TextInput
                        name="phone"
                        type="hidden"
                        value={phone}
                        onChange={e => setData('phone', phone)}
                        />
                        <TextInput
                        name="password"
                        error={errors.password}
                        value={data.password}
                        type="password"
                        onChange={e => setData('password', e.target.value)}
                        />
                    </FieldGroup>
                    </div>
                    <div className="flex items-center justify-end px-8 py-4 bg-gray-100 border-t border-gray-200">
                    <LoadingButton
                        loading={processing}
                        type="submit"
                        className="btn-indigo"
                    >
                        Проверить код
                    </LoadingButton>
                    </div>
                </form>
                </div>
    );
}