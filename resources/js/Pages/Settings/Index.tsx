
import MainLayout from '@/Layouts/MainLayout';
import { PageProps } from '@/types';
import { Link, useForm } from '@inertiajs/react';


function SettingsPage({auth}: PageProps) {
  const { data, setData, errors, post, processing } = useForm({
      phone: ""
  });

  const connections = auth.user.account.connections;

  
  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Настройки</h1>
      <div className='bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-full inline-block'>
            <Link className="btn-indigo focus:outline-none" href={"/settings/telegram-chat/create"}>Подключить личный телеграм</Link>
      </div>
      <div className="mt-10">
              <h1 className="mb-8 text-3xl font-bold">Зарегестрированные номера</h1>

        {connections.map((item, index) => (
          <div key={index}>
            <div>{item.phone}</div>
            <div className="cursor-pointer text-red-500 inline-block" onClick={(e) => {
              setData('phone', item.phone)
              
              post(route("settings.delete"))
            }}>Удалить</div>
          </div>
        ))}
        <div></div>
      </div>
    </div>
  );
}

SettingsPage.layout = (page: React.ReactNode) => (
    <MainLayout title="Reports" children={page} />
  );
  
export default SettingsPage;