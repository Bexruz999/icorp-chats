import React, { useState } from 'react';
import MainLayout from '@/Layouts/MainLayout';
import { PageProps } from '@/types';
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
    domain: string;
  } | null;
  onSuccess?: () => void;
}

const buttonClass =
  'bg-indigo-500 hover:bg-indigo-600 text-white px-4 py-2 rounded-lg font-semibold transition focus:outline-none focus:ring-2 focus:ring-indigo-400';

const AmoConnectionForm: React.FC<AmoConnectionFormProps> = ({ amo_connection, onSuccess }) => {
  const isEdit = !!amo_connection;
  const { data, setData, post, put, processing, errors } = useForm({
    uid: amo_connection?.uid || '',
    amojo_id: amo_connection?.amojo_id || '',
    secret_key: amo_connection?.secret_key || '',
    amo_account_id: amo_connection?.amo_account_id || '',
    domain: amo_connection?.domain || ''
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
      post('/settings/amo-connection', options);
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
        />
        {errors.uid && <div className="text-red-500 text-sm mt-1">{errors.uid}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">Amojo ID</label>
        <input
          type="text"
          value={data.amojo_id}
          onChange={e => setData('amojo_id', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
        />
        {errors.amojo_id && <div className="text-red-500 text-sm mt-1">{errors.amojo_id}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">Secret Key</label>
        <input
          type="text"
          value={data.secret_key}
          onChange={e => setData('secret_key', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
        />
        {errors.secret_key && <div className="text-red-500 text-sm mt-1">{errors.secret_key}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">Amo Account ID</label>
        <input
          type="text"
          value={data.amo_account_id}
          onChange={e => setData('amo_account_id', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
        />
        {errors.amo_account_id && <div className="text-red-500 text-sm mt-1">{errors.amo_account_id}</div>}
      </div>
      <div>
        <label className="block font-bold mb-1">Domain</label>
        <input
          type="text"
          value={data.domain}
          onChange={e => setData('domain', e.target.value)}
          className="input rounded-lg border border-gray-300 w-full px-3 py-2"
        />
        {errors.domain && <div className="text-red-500 text-sm mt-1">{errors.domain}</div>}
      </div>
      <button
        type="submit"
        className={buttonClass + ' w-full'}
        disabled={processing}
      >
        {isEdit ? 'Update' : 'Create'}
      </button>
    </form>
  );
};

function SettingsPage({ auth }: PageProps) {
  const { connections, amo_connections } = usePage<{
    connections: { id: number, phone: string }[],
    amo_connections: {
      id: number,
      uid: string,
      amojo_id: string,
      amo_account_id: string,
      domain: string
    }[]
  }>().props;

  const [modalOpen, setModalOpen] = useState(false);
  const [editAmo, setEditAmo] = useState<any | null>(null);

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
          Подключить личный телеграм
        </Link>
      </div>
      <div className="mt-10">
        <h1 className="mb-8 text-2xl font-semibold">Зарегестрированные номера</h1>
        <div className="bg-white border border-blue-200 rounded-xl shadow-sm p-6">
          <Table2
            columns={[{ label: 'номер', name: 'phone', colSpan: 2 }]}
            rows={connections}
            rowDelete={row => route('settings.delete', row.id)}
          />
        </div>
      </div>
      <div className="mt-10">
        <h1 className="mb-8 text-2xl font-semibold">AmoCRM Connection</h1>
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
                <td className="py-2 pr-4 font-bold text-indigo-600">Amo Account ID:</td>
                <td className="py-2 text-gray-800">{amo.amo_account_id}</td>
              </tr>
              <tr>
                <td className="py-2 pr-4 font-bold text-indigo-600">Domain:</td>
                <td className="py-2 text-gray-800">{amo.domain}</td>
              </tr>
              </tbody>
            </table>
            <div className="flex gap-2 mt-6 items-center">
              <button onClick={() => openModal(amo)} className={buttonClass + ' w-full'}>
                Edit
              </button>
              {/* Agar access_token bo'lsa Connected badge, bo'lmasa Connect tugmasi */}
              {'access_token' in amo && amo.access_token ? (
                <span className="inline-block bg-green-100 text-green-700 px-4 py-2 rounded-lg font-semibold w-full text-center">
                  Connected
                </span>
              ) : (
                <a href="/amocrm/connect" className={buttonClass + ' w-full text-center'}>
                  Connect
                </a>
              )}
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
              <span>No AmoCRM connection found.</span>
            </div>
            <button
              onClick={() => openModal(null)}
              className={buttonClass + ' w-full'}
            >
              Add AmoCRM Connection
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
