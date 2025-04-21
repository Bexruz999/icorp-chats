import React, { useState } from 'react';
import { Drawer, Button } from 'antd';
import AddRoleForm from './AddRoleForm';
import Create from '@/Pages/Roles/Create';

const RolesPage: React.FC = () => {
  const [open, setOpen] = useState(false);

  const closeDrawer = () => {setOpen(false);}

  return (
    <>
      <Button type="primary" onClick={() => setOpen(true)}>
        Добавить роль
      </Button>
      <Drawer
        title="Добавить роль"
        placement="right"
        width={500}
        onClose={() => setOpen(false)}
        open={open}
      >
        <Create closeDrawer={closeDrawer}/>
      </Drawer>
    </>
  );
};

export default RolesPage;
