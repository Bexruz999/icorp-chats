import React from 'react';
import { Head } from '@inertiajs/react';
import { Link, usePage, useForm, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import DeleteButton from '@/Components/Button/DeleteButton';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import { Bot, Shop } from '@/types';
import FieldGroup from '@/Components/Form/FieldGroup';
import Select from 'react-select';
import Table from '@/Components/Table/Table';

const Edit = () => {
  const { shop, bots } = usePage<{ shop: Shop, bots: [] }>().props;

  const { data, setData, errors, post, processing } = useForm({
    name: shop.name || '',
    bot_id: '',

    // NOTE: When working with Laravel PUT/PATCH requests and FormData
    // you SHOULD send POST request and fake the PUT request like this.
    _method: 'put'
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();

    // NOTE: We are using POST method here, not PUT/PATCH. See comment above.
    post(route('shops.update', shop.id));
  }

  function destroy() {
    if (confirm('Are you sure you want to delete this user?')) {
      router.delete(route('shops.destroy', shop.id));
    }
  }

  function restore() {
    if (confirm('Are you sure you want to restore this user?')) {
      router.put(route('shops.restore', shop.id));
    }
  }

  function addCategory() {
    router.get(route('categories.create.shop', shop.id));
  }

  function setBot(e: any) {
    data.bot_id = e.value;
  }

  return (
    <div>
      <Head title={`${data.name}`} />
      <div className="flex justify-start max-w-lg mb-8">
        <h1 className="text-3xl font-bold">
          <Link
            href={route('shops.index')}
            className="text-indigo-600 hover:text-indigo-700"
          >
            Магазины
          </Link>
          <span className="mx-2 font-medium text-indigo-600">/</span>
          {}
          {data.name}
        </h1>
      </div>
      <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
        <form onSubmit={handleSubmit}>
          <div className="grid gap-8 p-8 lg:grid-cols-2">
            <FieldGroup
              label="Название магазина"
              name="name"
              error={errors.name}
            >
              <TextInput
                name="name"
                error={errors.name}
                value={data.name}
                onChange={e => setData('name', e.target.value)}
              />
            </FieldGroup>
            <FieldGroup label="Выбрать Telegram Bot" name="bot_id">
              <Select defaultValue={bots.filter((bot: { selected: boolean }) => bot.selected)} options={bots}
                      onChange={e => setBot(e)} />
            </FieldGroup>
          </div>
          <div className="flex items-center px-8 py-4 bg-gray-100 border-t border-gray-200">
            <DeleteButton onDelete={destroy}>Удалить магазина</DeleteButton>
            <LoadingButton
              loading={processing}
              type="submit"
              className="ml-auto btn-indigo"
            >
              Обновлять
            </LoadingButton>
          </div>
        </form>
      </div>
      <h2 className="mt-12 mb-6 text-2xl font-bold">Категории</h2>
      <Table
        columns={[
          { label: 'Название', name: 'name' },
          { label: 'Родительский', name: 'parent_id' },
          { label: 'Phone', name: 'phone', colSpan: 2 }
        ]}
        rows={shop.categories}
        getRowDetailsUrl={row => route('categories.edit', row.id)}
      />

      <div className="mt-6">
        <LoadingButton
          loading={processing}
          type="button"
          onClick={addCategory}
          className="ml-auto btn-indigo"
        >
          Добавить категорию
        </LoadingButton>
      </div>
    </div>
  );
};

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
Edit.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Edit;
