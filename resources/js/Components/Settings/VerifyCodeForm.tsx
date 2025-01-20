import {useForm} from '@inertiajs/react';
import { useEffect } from 'react';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import FieldGroup from '@/Components/Form/FieldGroup';

export default function VerifyCodeForm({phone} : {phone: string}) {

    const { data, setData, errors, post, processing } = useForm({
        code: "",
        phone: phone
    });

    useEffect(() => {
        if (phone) {
            setData('phone', phone);
        }
    }, [phone]);
    
    function handleSubmitVerify(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(route('settings.verify-code'));
    }

    return (
            <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
                <form onSubmit={handleSubmitVerify}>
                    <div className="grid gap-8 p-8 lg:grid-cols-2">
                    <FieldGroup
                        label="Код авторизации"
                        name="code"
                        error={errors.code}
                    >
                        <TextInput
                        name="code"
                        error={errors.code}
                        value={data.code}
                        onChange={e => {
                            setData('code', e.target.value)
                        }}
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