import React from 'react';
import { useForm } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';

interface AmoConnectionFormProps {
  amo_connection?: {
    id?: number;
    uid: string;
    amojo_id: string;
    secret_key: string;
    amo_account_id: string;
    domain: string;
  } | null;
}

// Extend React.FC to include the layout property
type AmoConnectionFormComponent = React.FC<AmoConnectionFormProps> & {
  layout?: (page: React.ReactNode) => React.ReactNode;
};

const AmoConnectionForm: AmoConnectionFormComponent = ({ amo_connection }) => {
  const isEdit = !!amo_connection;
  const { data, setData, post, put, processing, errors } = useForm({
    uid: amo_connection?.uid || '',
    amojo_id: amo_connection?.amojo_id || '',
    secret_key: amo_connection?.secret_key || '',
    amo_account_id: amo_connection?.amo_account_id || '',
    domain: amo_connection?.domain || '',
  });

  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    if (isEdit && amo_connection?.id) {
      put(`/settings/amo-connection/${amo_connection.id}`);
    } else {
      post('/settings/amo-connection');
    }
  };

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">
        {isEdit ? 'Edit AmoCRM Connection' : 'Add AmoCRM Connection'}
      </h1>
      <form onSubmit={handleSubmit} className="space-y-4 max-w-lg">
        {/* form fields */}
        <div>
          <label className="block font-bold">UID</label>
          <input
            type="text"
            value={data.uid}
            onChange={e => setData('uid', e.target.value)}
            className="input"
          />
          {errors.uid && <div className="text-red-500">{errors.uid}</div>}
        </div>
        <div>
          <label className="block font-bold">Amojo ID</label>
          <input
            type="text"
            value={data.amojo_id}
            onChange={e => setData('amojo_id', e.target.value)}
            className="input"
          />
          {errors.amojo_id && <div className="text-red-500">{errors.amojo_id}</div>}
        </div>
        <div>
          <label className="block font-bold">Secret Key</label>
          <input
            type="text"
            value={data.secret_key}
            onChange={e => setData('secret_key', e.target.value)}
            className="input"
          />
          {errors.secret_key && <div className="text-red-500">{errors.secret_key}</div>}
        </div>
        <div>
          <label className="block font-bold">Amo Account ID</label>
          <input
            type="text"
            value={data.amo_account_id}
            onChange={e => setData('amo_account_id', e.target.value)}
            className="input"
          />
          {errors.amo_account_id && <div className="text-red-500">{errors.amo_account_id}</div>}
        </div>
        <div>
          <label className="block font-bold">Domain</label>
          <input
            type="text"
            value={data.domain}
            onChange={e => setData('domain', e.target.value)}
            className="input"
          />
          {errors.domain && <div className="text-red-500">{errors.domain}</div>}
        </div>
        <button
          type="submit"
          className="bg-indigo-500 text-white px-4 py-2 rounded"
          disabled={processing}
        >
          {isEdit ? 'Update' : 'Create'}
        </button>
      </form>
    </div>
  );
};

AmoConnectionForm.layout = (page: React.ReactNode) => (
  <MainLayout title="AmoCRM Connection" children={page} />
);

export default AmoConnectionForm;
