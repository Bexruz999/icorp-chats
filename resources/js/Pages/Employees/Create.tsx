import { Link, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import SelectInput from '@/Components/Form/SelectInput';
import FieldGroup from '@/Components/Form/FieldGroup';
import React from 'react';

const Create = () => {
 const { amoUsers, connections } = usePage<{ amoUsers: { amojo_id: string; name: string }[]; connections: { id: string; phone?: string; name?: string }[] }>().props;

  const { data, setData, errors, post, processing } = useForm({
    first_name: '',
    last_name: '',
    email: '',
    password: '',
    owner: '0',
    photo: '',
    amojo_id: '',
    connection_id: ''
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post(route('employees.store'));
  }

  return (
    <div>
      <div>
        <h1 className="mb-8 text-3xl font-bold">
          <Link
            href={route('users')}
            className="text-indigo-600 hover:text-indigo-700"
          >
            Сотрудники
          </Link>
          <span className="font-medium text-indigo-600"> /</span> Создать
        </h1>
      </div>
      <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
        <form onSubmit={handleSubmit}>
          <div className="grid gap-8 p-8 lg:grid-cols-2">
            <FieldGroup
              label="First Name"
              name="first_name"
              error={errors.first_name}
            >
              <TextInput
                name="first_name"
                error={errors.first_name}
                value={data.first_name}
                onChange={e => setData('first_name', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup
              label="Last Name"
              name="last_name"
              error={errors.last_name}
            >
              <TextInput
                name="last_name"
                error={errors.last_name}
                value={data.last_name}
                onChange={e => setData('last_name', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Email" name="email" error={errors.email}>
              <TextInput
                name="email"
                type="email"
                error={errors.email}
                value={data.email}
                onChange={e => setData('email', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup
              label="Password"
              name="password"
              error={errors.password}
            >
              <TextInput
                name="password"
                type="password"
                error={errors.password}
                value={data.password}
                onChange={e => setData('password', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Amojo_id" name="amojo_id" error={errors.amojo_id}>
              <SelectInput
                name="amojo_id"
                error={errors.amojo_id}
                value={data.amojo_id}
                onChange={e => setData('amojo_id', e.target.value)}
                options={
                  amoUsers.length > 0
                    ? [
                        { value: '', label: 'Выберите Amojo ID' },
                        ...amoUsers.map(user => ({
                          value: user.amojo_id,
                          label: user.name,
                        }))
                      ]
                    : [{ value: '', label: 'Нет доступных Amojo ID' }]
                }
                disabled={amoUsers.length === 0}
              />
            </FieldGroup>
            <FieldGroup label="Connection" name="connection_id" error={errors.connection_id}>
              <SelectInput
                name="connection_id"
                error={errors.connection_id}
                value={data.connection_id}
                onChange={e => setData('connection_id', e.target.value)}
                options={
                  connections.length > 0
                    ? [
                        { value: '', label: 'Выберите подключение' },
                        ...connections.map(conn => ({
                          value: conn.id,
                          label: conn.phone || conn.name || `ID: ${conn.id}`,
                        }))
                      ]
                    : [{ value: '', label: 'Нет доступных подключений' }]
                }
                disabled={connections.length === 0}
              />
            </FieldGroup>
          </div>
          <div className="flex items-center justify-end px-8 py-4 bg-gray-100 border-t border-gray-200">
            <LoadingButton loading={processing} type="submit" className="btn-indigo">
              Создать
            </LoadingButton>
          </div>
        </form>
      </div>
    </div>
  );
};

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
Create.layout = (page: React.ReactNode) => (
  <MainLayout title="Create User" children={page} />
);

export default Create;
