import { Link, useForm, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import SelectInput from '@/Components/Form/SelectInput';
import FieldGroup from '@/Components/Form/FieldGroup';
import FileInput from '@/Components/Form/FileInput';
import React from 'react';

const Create = () => {

  const { categories } = usePage<{ categories: [], shops: [] }>().props;
  const { data, setData, errors, post, processing } = useForm({
    name: '',
    category: '',
    price: '',
    discount_price: '',
    description: '',
    short_description: '',
    image: ''
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    post(route('products.store'));
  }

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">
        <Link
          href={route('products.index')}
          className="text-indigo-600 hover:text-indigo-700"
        >
          Продукция
        </Link>
        <span className="font-medium text-indigo-600"> /</span> Создать
      </h1>
      <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
        <form onSubmit={handleSubmit}>
          <div className="grid gap-8 p-8 lg:grid-cols-2">
            <FieldGroup label="Название" name="name" error={errors.name}>
              <TextInput
                name="name"
                error={errors.name}
                value={data.name}
                onChange={e => setData('name', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Фотография" name="image" error={errors.image}>
              <FileInput
                name="photo"
                accept="image/*"
                error={errors.image}
                value={data.image}
                onChange={photo => {
                  setData('image', photo as unknown as string);
                }}
              />
            </FieldGroup>

            <FieldGroup label="Описание" name="description" error={errors.description}>
              <TextInput
                name="description"
                error={errors.description}
                value={data.description}
                onChange={e => setData('description', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Короткий Описание" name="short_description" error={errors.short_description}>
              <TextInput
                name="short_description"
                error={errors.short_description}
                value={data.short_description}
                onChange={e => setData('short_description', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Категория" name="category_id" error={errors.category}>
              <SelectInput
                name="category"
                error={errors.category}
                value={data.category}
                onChange={e => setData('category', e.target.value)}
                options={categories}
              />
            </FieldGroup>

            <FieldGroup label="Цена" name="price" error={errors.price}>
              <TextInput
                name="price"
                error={errors.price}
                value={data.price}
                onChange={e => setData('price', e.target.value)}
              />
            </FieldGroup>

            <FieldGroup label="Скидочная Цена" name="discount_price" error={errors.discount_price}>
              <TextInput
                name="discount_price"
                error={errors.discount_price}
                value={data.discount_price}
                onChange={e => setData('discount_price', e.target.value)}
              />
            </FieldGroup>
          </div>
          <div className="flex items-center justify-end px-8 py-4 bg-gray-100 border-t border-gray-200">
            <LoadingButton
              loading={processing}
              type="submit"
              className="btn-indigo"
            >
              Создать продукт
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
  <MainLayout title="Create Organization" children={page} />
);

export default Create;
