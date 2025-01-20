
import MainLayout from '@/Layouts/MainLayout';
import { PageProps } from '@/types';
import { Link, useForm, usePage } from '@inertiajs/react';
import Table2 from '@/Components/Table/Table2';



function SettingsPage({auth}: PageProps) {

  const {connections} = usePage<{
    connections: {
      id: number,
      phone: string
    }[]
  }>().props;

  console.log(connections);

  const { data, setData, errors, post, processing } = useForm({
      connections: connections
  });

  function deleteModal(row: {}) {

    if (confirm('Хотите удалить?')) {
      console.log(row);
    }
  }


  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Настройки</h1>
      <div className='bg-blue-500 hover:bg-blue-700 text-white font-bold rounded-full inline-block'>
            <Link className="btn-indigo focus:outline-none" href={"/settings/telegram-chat/create"}>Подключить личный телеграм</Link>
      </div>
      <div className="mt-10">
        <h1 className="mb-8 text-3xl font-bold">Зарегестрированные номера</h1>
        <Table2
          columns={[
            { label: 'номер', name: 'phone', colSpan: 2}
          ]}
          rows={data.connections}
          rowDelete={row => route('settings.delete', row.id)}
        />
        <div></div>
      </div>
    </div>
  );
}

SettingsPage.layout = (page: React.ReactNode) => (
  <MainLayout title="Reports" children={page} />
);

export default SettingsPage;
