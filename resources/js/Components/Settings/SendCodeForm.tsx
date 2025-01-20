import {useForm} from '@inertiajs/react';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import FieldGroup from '@/Components/Form/FieldGroup';
import { SetStateAction } from 'react';

export default function SendCodeForm({onChange}: {onChange: React.Dispatch<SetStateAction<string>>}) {
    const { data, setData, errors, post, processing } = useForm({
        phone: ""
    });

    function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
        e.preventDefault();
        post(route('settings.send-code'));
    }
    return (
        <form onSubmit={handleSubmit}>
        <div className="grid gap-8 p-8 lg:grid-cols-2">
        <FieldGroup
            label="Номер телефона"
            name="phone"
            error={errors.phone}
        >
            <TextInput
            name="phone"
            error={errors.phone}
            value={data.phone}
            onChange={e => {
                {onChange}
                setData('phone', e.target.value)
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
            Отправить проверочный код
        </LoadingButton>
        </div>
    </form>
    )
}