import { Link, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import FieldGroup from '@/Components/Form/FieldGroup';
import React from 'react';
import FileInput from '@/Components/Form/FileInput';

const Create = () => {
  const { shop } = usePage<{shop: number}>().props

  const { data, setData, errors, post, processing } = useForm({
    name: '',
    description: '',
    image: '',
    shop_id: shop || '',
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post(route('categories.store'));
  }

  return (
    <div>
      <div>
        <h1 className="mb-8 text-3xl font-bold">
          <Link
            href={route('categories.index')}
            className="text-indigo-600 hover:text-indigo-700"
          >
            Категории
          </Link>
          <span className="font-medium text-indigo-600"> /</span> Создать
        </h1>
      </div>
      <div className="overflow-hidden bg-white rounded shadow">
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
            <TextInput name="shop_id" type="hidden" value={data.shop_id}/>

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
