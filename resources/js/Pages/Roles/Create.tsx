import React from 'react';
import { useForm } from '@inertiajs/react';

interface PermissionRow {
  key: string;
  label: string;
  view: boolean;
  edit: boolean;
  remove: boolean;
}

const initialPermissions: PermissionRow[] = [
  { key: 'dashboard', label: 'Дашборд', view: false, edit: false, remove: false },
  { key: 'orders', label: 'Заказы', view: false, edit: false, remove: false },
  { key: 'customers', label: 'Клиенты', view: false, edit: false, remove: false },
  { key: 'chat', label: 'Чат', view: false, edit: false, remove: false },
  { key: 'categories', label: 'Категории', view: false, edit: false, remove: false },
  { key: 'products', label: 'Продукты', view: false, edit: false, remove: false },
  { key: 'discount', label: 'Скидка', view: false, edit: false, remove: false }
];

interface FormData {
  roleName: string;
  permissions: PermissionRow[];
}

interface closeDrawer {
  closeDrawer: () => void;
}

const AddRoleForm: React.FC<closeDrawer> = ({ closeDrawer }) => {
  // Initialize form state using Inertia's useForm hook.
  const { data, setData, post, processing, reset, errors } = useForm<FormData>({
    roleName: '',
    permissions: initialPermissions
  });

  /**
   * Updates the checkbox value for a specific permission row.
   * @param index - The index of the permission row.
   * @param field - The permission field to toggle ("view", "edit", or "remove").
   */
  const handleCheckboxChange = (index: number, field: keyof PermissionRow) => {
    const updatedPermissions = data.permissions.map((row, i) => {
      if (i === index) {
        return { ...row, [field]: !row[field] };
      }
      return row;
    });
    setData('permissions', updatedPermissions);
  };

  /**
   * Handles the form submission.
   * Sends a POST request using Inertia to the specified endpoint.
   */
  const handleSubmit = (e: React.FormEvent) => {
    e.preventDefault();
    // Change '/roles' to your actual endpoint
    post('/roles');

    reset();
    closeDrawer();
  };

  /**
   * Handles the reset action when "Отмена" is clicked.
   */
  const handleReset = () => {
    reset();
    closeDrawer();
  };

  return (
    <form onSubmit={handleSubmit} className="bg-white rounded shadow p-6 w-full max-w-lg">
      <h2 className="text-xl font-semibold mb-4">Добавить роль</h2>

      {/* Role Name Input */}
      <div className="mb-4">
        <label htmlFor="roleName" className="block text-sm font-medium mb-1">
          Название
        </label>
        <input
          id="roleName"
          name="roleName"
          type="text"
          placeholder="Введите название"
          value={data.roleName}
          onChange={(e) => setData('roleName', e.target.value)}
          className="w-full border border-gray-300 rounded px-3 py-2 focus:outline-none focus:border-indigo-500"
        />
        {errors.roleName && <div className="text-red-500 text-sm">{errors.roleName}</div>}
      </div>

      {/* Permissions Table */}
      <div className="overflow-x-auto">
        <table className="min-w-full border-collapse">
          <thead>
          <tr className="border-b">
            <th className="py-2 text-left">Меню</th>
            <th className="py-2 text-center">Просмотр</th>
            <th className="py-2 text-center">Редактировать</th>
            <th className="py-2 text-center">Удалить</th>
          </tr>
          </thead>
          <tbody>
          {data.permissions.map((row, index) => (
            <tr key={row.label} className="border-b last:border-0">
              <td className="py-2">
                {row.label}
                {/* Hidden input to send the label */}
                <input type="hidden" name={`permissions[${index}][label]`} value={row.label} />
              </td>
              <td className="py-2 text-center">
                <input
                  type="checkbox"
                  name={`permissions[${index}][view]`}
                  checked={row.view}
                  onChange={() => handleCheckboxChange(index, 'view')}
                  className="w-5 h-5 border-2 border-gray-300 rounded appearance-none
                               checked:bg-indigo-600 checked:border-transparent
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </td>
              <td className="py-2 text-center">
                <input
                  type="checkbox"
                  name={`permissions[${index}][edit]`}
                  checked={row.edit}
                  onChange={() => handleCheckboxChange(index, 'edit')}
                  className="w-5 h-5 border-2 border-gray-300 rounded appearance-none
                               checked:bg-indigo-600 checked:border-transparent
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </td>
              <td className="py-2 text-center">
                <input
                  type="checkbox"
                  name={`permissions[${index}][remove]`}
                  checked={row.remove}
                  onChange={() => handleCheckboxChange(index, 'remove')}
                  className="w-5 h-5 border-2 border-gray-300 rounded appearance-none
                               checked:bg-indigo-600 checked:border-transparent
                               focus:outline-none focus:ring-2 focus:ring-indigo-500"
                />
              </td>
            </tr>
          ))}
          </tbody>
        </table>
      </div>

      {/* Action Buttons */}
      <div className="mt-4 flex justify-end">
        <button
          type="button"
          onClick={handleReset}
          className="mr-2 px-4 py-2 bg-gray-200 text-gray-700 rounded hover:bg-gray-300"
          disabled={processing}
        >
          Отмена
        </button>
        <button
          type="submit"
          className="px-4 py-2 bg-indigo-600 text-white rounded hover:bg-indigo-700"
          disabled={processing}
        >
          {processing ? 'Отправка...' : 'Добавить'}
        </button>
      </div>
    </form>
  );
};

export default AddRoleForm;
