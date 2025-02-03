import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import FilterBar from '@/Components/FilterBar/FilterBar';
import Pagination from '@/Components/Pagination/Pagination';
import { Category, PaginatedData } from '@/types';
import Table from '@/Components/Table/Table';

const Index = () => {
  const { categories } = usePage<{ categories: PaginatedData<Category> }>().props;

  const {
    data,
    meta: { links }
  } = categories;

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">
        Категория
      </h1>
      <div className="flex items-center justify-between mb-6">
        <FilterBar />
        {/*<Link className="btn-indigo focus:outline-none" href={route('shops.create')}>
          <span>Создать</span>
        </Link>*/}
      </div>
      <Table
        columns={[
          {
            label: 'Название',
            name: 'name',

            renderCell: row => (
              <>
                {row.image && (
                  <img
                    src={'/storage/' + row.image}
                    alt={row.name}
                    className="w-5 h-5 mr-2 rounded-full"
                  />
                )}
                <>{row.name}</>
              </>
            )
          }, {
            label: 'Магазин',
            name: 'shop_id',
            renderCell: row => (
              <>{row.shop.name}</>
            )
          }
        ]}
        rows={data}
        getRowDetailsUrl={row => route('categories.edit', row.id)}
      />
      <Pagination links={links} />
    </div>
  );
};

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
Index.layout = (page: React.ReactNode) => (
  <MainLayout title="Магазины" children={page} />
);

export default Index;
