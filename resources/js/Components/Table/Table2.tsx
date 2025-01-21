import { Link, router } from '@inertiajs/react';
import get from 'lodash/get';
import { ChevronRight, Table, Trash2 } from 'lucide-react';
import { useState } from 'react';

interface TableProps<T> {
  columns: {
    name: string;
    label: string;
    colSpan?: number;
    renderCell?: (row: T) => React.ReactNode;
  }[];
  rows: T[];
  rowDelete?: (row: T) => string;
}


function deleteModal(e) {

  if (confirm('Хотите удалить?')) {
    router.delete(e.currentTarget.getAttribute('delete-url'), );
  }
}

export default function Table2<T>({
  columns = [],
  rows = [],
  rowDelete
}: TableProps<T>) {

  return (
    <div className="overflow-x-auto bg-white rounded shadow">
      <table className="w-full whitespace-nowrap">
        <thead>
          <tr className="font-bold text-left">
            {columns?.map(column => (
              <th
                key={column.label}
                colSpan={column.colSpan ?? 1}
                className="px-6 pt-5 pb-4"
              >
                {column.label}
              </th>
            ))}
          </tr>
        </thead>
        <tbody>
          {/* Empty state */}
          {rows?.length === 0 && (
            <tr>
              <td
                className="px-6 py-24 border-t text-center"
                colSpan={columns.length}
              >
                Данные не найдены.
              </td>
            </tr>
          )}
          {rows?.map((row, index) => {
            return (
              <tr key={index} className="hover:bg-gray-100 focus-within:bg-gray-100">
                {columns.map(column => {
                  return (
                    <td key={column.name} className="border-t">
                      <Link
                        tabIndex={-1}
                        href=""
                        className="flex items-center px-6 py-4 focus:text-indigo focus:outline-none"
                      >
                        {column.renderCell?.(row) ??
                          get(row, column.name) ??
                          'N/A'}
                      </Link>
                    </td>
                  );
                })}
                <td className="w-px border-t">
                  <div className="flex items-center px-4 focus:outline-none">
                    <button key={1} delete-url={rowDelete?.(row)!} onClick={deleteModal}>
                      <Trash2 size={28} fontWeight="bold" className="text-red-600" />
                    </button>
                  </div>
                </td>
              </tr>
            );
          })}
        </tbody>
      </table>
    </div>
  );
}
