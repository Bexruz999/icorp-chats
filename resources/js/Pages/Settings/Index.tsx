
import MainLayout from '@/Layouts/MainLayout';
import { Link } from '@inertiajs/react';

function SettingsPage() {
  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Настройки</h1>
      <div className='bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-full inline-block'>
            <Link className="btn-indigo focus:outline-none" href={"/settings/telegram-chat/create"}>Подключить личный телеграм</Link>
      </div>
    </div>
  );
}

SettingsPage.layout = (page: React.ReactNode) => (
    <MainLayout title="Reports" children={page} />
  );
  
export default SettingsPage;