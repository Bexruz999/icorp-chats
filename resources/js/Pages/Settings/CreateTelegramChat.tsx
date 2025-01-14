
import MainLayout from '@/Layouts/MainLayout';
import { Link, useForm } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import SendCodeForm from '@/Components/Settings/SendCodeForm';
import VerifyCodeForm from '@/Components/Settings/VerifyCodeForm';
import VerifyPasswordForm from '@/Components/Settings/VerifyPasswordForm';

function CreateTelegramChatPage({state, phoneNumber = ""} : {state: number, phoneNumber?: string}) {
    const [phone, setPhone] = useState(phoneNumber);
    let form;

    useEffect(() => {
        setPhone(phoneNumber);
    }, [phoneNumber]);
    
    // 1 - get phone
    // 2 - verify code
    // 3 - check password for 2fa
    if(state == 1) {
        form = <SendCodeForm onChange={setPhone}/>
    } else if(state == 2) {
        console.log("Received phone number:", phoneNumber);
        console.log("Phone number:", phone);
        form = <VerifyCodeForm phone={phone}/>
    } else if(state = 3) {
        form = <VerifyPasswordForm phone={phone}/>
    }

    return (
        <div>
          <h1 className="mb-8 text-3xl font-bold">
            <Link
              href={route('settings')}
              className="text-indigo-600 hover:text-indigo-700"
            >
              Настройки
            </Link>
            <span className="font-medium text-indigo-600"> /</span> Создать
          </h1>
            <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
                {form}
            </div>

        </div>
    );
};

CreateTelegramChatPage.layout = (page: React.ReactNode) => (
    <MainLayout title="Reports" children={page} />
  );
  
export default CreateTelegramChatPage;