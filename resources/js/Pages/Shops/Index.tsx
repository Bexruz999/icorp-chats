import { Link, usePage } from '@inertiajs/react';
import MainLayout from '@/Layouts/MainLayout';
import FilterBar from '@/Components/FilterBar/FilterBar';
import Pagination from '@/Components/Pagination/Pagination';
import { PaginatedData, Shop } from '@/types';
import Table from '@/Components/Table/Table';

const Index = () => {
  const { shops } = usePage<{ shops: PaginatedData<Shop> }>().props;

  const {
    data,
    meta: { links }
  } = shops;

  console.log(data);

  return (
    <div>
      <h1 className="mb-8 text-3xl font-bold">
        Магазины
      </h1>
      <div className="flex items-center justify-between mb-6">
        <FilterBar />
        <Link className="btn-indigo focus:outline-none" href={route('shops.create')}>
          <span>Создать</span>
        </Link>
      </div>
      <Table
        columns={[
          {
            label: 'Название',
            name: 'name',

            renderCell: row => (
              <>
                {row.logo && (
                  <img
                    src={row.logo}
                    alt={row.name}
                    className="w-5 h-5 mr-2 rounded-full"
                  />
                )}
                <>{row.name}</>
              </>
            )
          },
          {
            label: 'Slug',
            name: 'slug',

            renderCell: row => (
              <>{row.slug}</>
            )
          },
          {
            label: 'Бот',
            name: 'bot',

            renderCell: row => (
              <>{row.bot ? row.bot.name: ''}</>
            )
          }
        ]}
        rows={data}
        getRowDetailsUrl={row => route('shops.edit', row.id)}
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
