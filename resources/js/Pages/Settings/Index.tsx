import React, { useState } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { Link, usePage, useForm } from '@inertiajs/react';
import Table2 from '@/Components/Table/Table2';
import EditModal from '@/Components/Settings/EditModal';

interface AmoConnectionFormProps {
  amo_connection?: {
    id?: number;
    uid: string;
    amojo_id: string;
    secret_key: string;
    amo_account_id: string;
    base_domain: string; // domain -> base_domain
  } | null;
  onSuccess?: () => void;
}

const buttonClass =
  'bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-indigo-400';

const AmoConnectionForm: React.FC<AmoConnectionFormProps> = ({ amo_connection, onSuccess }) => {
  const isEdit = !!amo_connection;
  const { data, setData, post, put, processing, errors } = useForm({
    uid: amo_connection?.uid || '',
    amojo_id: isEdit ? (amo_connection?.amojo_id || '') : '',
    secret_key: amo_connection?.secret_key || '',
    amo_account_id: amo_connection?.amo_account_id || '',
    base_domain: amo_connection?.base_domain || ''
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    const options = {
      onSuccess: () => {
        if (onSuccess) onSuccess();
      }
    };
    if (isEdit && amo_connection?.id) {
      put(`/settings/amo-connection/${amo_connection.id}`, options);
    } else {
      const { amojo_id, ...rest } = data;
      post('/settings/amo-connection', { ...options, data: rest });
    }
  };

  return (
    <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
      <div>
        <label className="block font-bold mb-1">UID</label>
        <input
          type="text"
          value={data.uid}
          onChange={e => setData('uid', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
          placeholder="Введите UID"
        />
        {errors.uid && <div className="text-red-500 text-sm mt-1">{errors.uid}</div>}
      </div>
      {isEdit && (
        <div>
          <label className="block font-bold mb-1">Amojo ID</label>
          <input
            type="text"
            value={data.amojo_id}
            onChange={e => setData('amojo_id', e.target.value)}
            className="input rounded-lg border border-gray-300 w-full px-3 py-2"
            placeholder="Введите Amojo ID"
          />
          {errors.amojo_id && <div className="text-red-500 text-sm mt-1">{errors.amojo_id}</div>}
        </div>
      )}
      <div>
        <label className="block font-bold mb-1">Секретный ключ</label>
        <input
          type="text"
          value={data.secret_key}
          onChange={e => setData('secret_key', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
          placeholder="Введите секретный ключ"
        />
        {errors.secret_key && <div className="text-red-500 text-sm mt-1">{errors.secret_key}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">ID аккаунта Amo</label>
        <input
          type="text"
          value={data.amo_account_id}
          onChange={e => setData('amo_account_id', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
          placeholder="Введите ID аккаунта Amo"
        />
        {errors.amo_account_id && <div className="text-red-500 text-sm mt-1">{errors.amo_account_id}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">Базовый домен</label>
        <input
          type="text"
          value={data.base_domain}
          onChange={e => setData('base_domain', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
          placeholder="Введите базовый домен"
        />
        {errors.base_domain && <div className="text-red-500 text-sm mt-1">{errors.base_domain}</div>}
      </div>
      <button
        type="submit"
        className={buttonClass + ' w-full'}
        disabled={processing}
      >
        {isEdit ? 'Сохранить' : 'Создать'}
      </button>
    </form>
  );
};

function SettingsPage() {
  const { connections, amo_connections } = usePage<{
    connections: { id: number, phone: string }[],
    amo_connections: {
      id: number,
      uid: string,
      amojo_id: string,
      amo_account_id: string,
      base_domain: string // domain -> base_domain
    }[]
  }>().props;

  const [modalOpen, setModalOpen] = useState(false);
  const [editAmo, setEditAmo] = useState<any | null>(null);

  const { delete: deleteAmo, processing: deleting } = useForm();

  const openModal = (amoConnection: any | null = null) => {
    setEditAmo(amoConnection);
    setModalOpen(true);
  };

  const closeModal = () => {
    setModalOpen(false);
    setEditAmo(null);
  };

  const amo = amo_connections?.[0];

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">Настройки</h1>
      <div className="mb-6">
        <Link className={buttonClass} href={'/settings/telegram-chat/create'}>
          Подключить личный Telegram
        </Link>
      </div>
      <div className="mt-10">
        <h1 className="mb-8 text-2xl font-semibold">Зарегистрированные номера</h1>
        <div className="bg-white border border-blue-200 rounded-xl shadow-sm p-6">
          <Table2
            columns={[{ label: 'Номер', name: 'phone', colSpan: 2 }]}
            rows={connections}
            rowDelete={row => route('settings.delete', row.id)}
          />
        </div>
      </div>
      <div className="mt-10">
        <h1 className="mb-8 text-2xl font-semibold">Подключение AmoCRM</h1>
        {amo ? (
          <div className="bg-white border border-blue-200 rounded-xl shadow-sm p-6">
            <table className="min-w-full text-sm">
              <tbody>
              <tr>
                <td className="py-2 pr-4 font-bold text-indigo-600">UID:</td>
                <td className="py-2 text-gray-800">{amo.uid}</td>
              </tr>
              <tr>
                <td className="py-2 pr-4 font-bold text-indigo-600">Amojo ID:</td>
                <td className="py-2 text-gray-800">{amo.amojo_id}</td>
              </tr>
              <tr>
                <td className="py-2 pr-4 font-bold text-indigo-600">ID аккаунта Amo:</td>
                <td className="py-2 text-gray-800">{amo.amo_account_id}</td>
              </tr>
              <tr>
                <td className="py-2 pr-4 font-bold text-indigo-600">Базовый домен:</td>
                <td className="py-2 text-gray-800">{amo.base_domain}</td>
              </tr>
              </tbody>
            </table>
            <div className="flex gap-2 mt-6 items-center">
              <button onClick={() => openModal(amo)} className={buttonClass + ' w-full'}>
                Редактировать
              </button>
              {'access_token' in amo && amo.access_token ? (
                <span className="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-lg font-semibold w-full text-center">
                  Подключено
                </span>
              ) : (
                <a href="/amocrm/connect" className={buttonClass + ' w-full text-center'}>
                  Подключить
                </a>
              )}
              <button
                type="button"
                className="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg font-semibold transition w-full"
                disabled={deleting}
                onClick={() => {
                  if (window.confirm('Вы уверены, что хотите удалить это подключение AmoCRM?')) {
                    deleteAmo(`/settings/amo-connection/${amo.id}`, {
                      method: 'delete',
                      onSuccess: () => {},
                    });
                  }
                }}
              >
                Удалить
              </button>
            </div>
          </div>
        ) : (
          <div className="bg-white border border-blue-200 rounded-xl shadow-sm p-6 flex flex-col items-center">
            <div
              className="flex items-center gap-2 bg-blue-50 border border-blue-200 text-blue-700 px-4 py-3 rounded-lg mb-4 w-full justify-center">
              <svg className="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" strokeWidth="2"
                   viewBox="0 0 24 24">
                <path strokeLinecap="round" strokeLinejoin="round"
                      d="M13 16h-1v-4h-1m1-4h.01M12 20a8 8 0 100-16 8 8 0 000 16z" />
              </svg>
              <span>Подключение AmoCRM не найдено.</span>
            </div>
            <button
              onClick={() => openModal(null)}
              className={buttonClass + ' w-full'}
            >
              Добавить подключение AmoCRM
            </button>
          </div>
        )}
      </div>
      <EditModal open={modalOpen} onClose={closeModal}>
        <AmoConnectionForm amo_connection={editAmo} onSuccess={closeModal} />
      </EditModal>
    </div>
  );
}

SettingsPage.layout = (page: React.ReactNode) => (
  <MainLayout title="Reports" children={page} />
);

export default SettingsPage;
