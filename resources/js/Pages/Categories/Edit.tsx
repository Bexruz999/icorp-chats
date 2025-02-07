import React from 'react';
import { Head } from '@inertiajs/react';
import { usePage, useForm, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import DeleteButton from '@/Components/Button/DeleteButton';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import { Category } from '@/types';
import FieldGroup from '@/Components/Form/FieldGroup';
import Table from '@/Components/Table/Table';
import FileInput from '@/Components/Form/FileInput';

const Edit = () => {
  const { category } = usePage<{ category: Category }>().props;

  const { data, setData, errors, post, processing } = useForm({
    name: category.name || '',
    description: category.description || '',
    bot_id: '',
    image: '',

    // NOTE: When working with Laravel PUT/PATCH requests and FormData
    // you SHOULD send POST request and fake the PUT request like this.
    _method: 'put'
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();

    // NOTE: We are using POST method here, not PUT/PATCH. See comment above.
    post(route('categories.update', category.id));
  }

  function addProduct() {
    router.get(route('products.create.category', category.id));
  }

  function destroy() {
    if (confirm('Are you sure you want to delete this user?')) {
      router.delete(route('categories.destroy', category.id));
    }
  }

  function restore() {
    if (confirm('Are you sure you want to restore this user?')) {
      router.put(route('categories.restore', category.id));
    }
  }

  function setBot(e: any) {
    data.bot_id = e.value;
  }

  return (
    <div>
      <Head title={`${data.name}`} />
      <div className="flex justify-start max-w-lg mb-8">
        <h1 className="text-3xl font-bold">
          {data.name}
        </h1>
      </div>
      <div className=" overflow-hidden bg-white rounded shadow">
        <form onSubmit={handleSubmit}>
          <div className="grid gap-8 p-8 lg:grid-cols-1">
            <FieldGroup
              label="Название категории"
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

            <FieldGroup
              label="Описание категории"
              name="description"
              error={errors.description}
            >
              <TextInput
                name="name"
                error={errors.description}
                value={data.description}
                onChange={e => setData('description', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup
              label="Изображение"
              name="image"
              error={errors.image}
            >
              <FileInput
                name="image"
                accept="image/*"
                error={errors.image}
                value={data.image}
                onChange={photo => {
                  setData('image', photo as unknown as string);
                }}
              />
            </FieldGroup>

          </div>
          <div className="flex items-center px-8 py-4 bg-gray-100 border-t border-gray-200">
            <DeleteButton onDelete={destroy}>Удалить</DeleteButton>
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
      <h2 className="mt-12 mb-6 text-2xl font-bold">Продукты</h2>
      <Table
        columns={[
          { label: 'Название', name: 'name' },
          { label: 'Phone', name: 'phone', colSpan: 2 }
        ]}
        rows={category.products}
        getRowDetailsUrl={row => route('products.edit', row.id)}
      />

      <div className="mt-6">
        <LoadingButton
          loading={processing}
          type="button"
          onClick={addProduct}
          className="ml-auto btn-indigo"
        >
          Добавить Продукт
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
