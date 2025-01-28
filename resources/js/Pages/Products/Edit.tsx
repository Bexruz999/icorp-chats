import React from 'react';
import { Head } from '@inertiajs/react';
import { Link, usePage, useForm, router } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import DeleteButton from '@/Components/Button/DeleteButton';
import LoadingButton from '@/Components/Button/LoadingButton';
import TextInput from '@/Components/Form/TextInput';
import SelectInput from '@/Components/Form/SelectInput';
import TrashedMessage from '@/Components/Messages/TrashedMessage';
import { Product } from '@/types';
import FieldGroup from '@/Components/Form/FieldGroup';
import FileInput from '@/Components/Form/FileInput';

const Edit = () => {
  const { product, categories } = usePage<{ product: Product }>().props;
  const { data, setData, errors, put, processing } = useForm({
    name: product.name || '',
    description: product.description || '',
    short_description: product.short_description || '',
    image: product.image || '',
    category_id: product.category_id || '',
    price: product.price || '',
    discount_price: product.discount_price || '',
  });

  function handleSubmit(e: React.FormEvent<HTMLFormElement>) {
    e.preventDefault();
    put(route('organizations.update', product.id));
  }

  function destroy() {
    if (confirm('Are you sure you want to delete this organization?')) {
      router.delete(route('organizations.destroy', product.id));
    }
  }

  function restore() {
    if (confirm('Are you sure you want to restore this organization?')) {
      router.put(route('organizations.restore', product.id));
    }
  }

  return (
    <div>
      <Head title={data.name} />
      <h1 className="mb-8 text-3xl font-bold">
        <Link
          href={route('products.index')}
          className="text-indigo-600 hover:text-indigo-700"
        >
          Продукция
        </Link>
        <span className="mx-2 font-medium text-indigo-600">/</span>
        {data.name}
      </h1>
      <div className="max-w-3xl overflow-hidden bg-white rounded shadow">
        <form onSubmit={handleSubmit}>
          <div className="grid gap-8 p-8 lg:grid-cols-2">
            <FieldGroup label="Name" name="name" error={errors.name}>
              <TextInput
                name="name"
                error={errors.name}
                value={data.name}
                onChange={e => setData('name', e.target.value)}
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

            <FieldGroup label="Категория" name="category_id" error={errors.category_id}>
              <SelectInput
                name="country"
                error={errors.category_id}
                value={data.category_id}
                onChange={e => setData('category_id', e.target.value)}
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
                name="price"
                error={errors.price}
                value={data.price}
                onChange={e => setData('price', e.target.value)}
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

          </div>
          <div className="flex items-center px-8 py-4 bg-gray-100 border-t border-gray-200">
            <DeleteButton onDelete={destroy}>
              Удалить продукт
            </DeleteButton>
            <LoadingButton
              loading={processing}
              type="submit"
              className="ml-auto btn-indigo"
            >
              Обновить продукт
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
Edit.layout = (page: React.ReactNode) => <MainLayout children={page} />;

export default Edit;
