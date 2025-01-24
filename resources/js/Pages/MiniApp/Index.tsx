import { Link } from '@inertiajs/react';
import MiniAppLayout from '@/Layouts/MiniAppLayout';
import styled from 'styled-components';
import { useState } from 'react';
import { Card } from '@/Components/Cards/Cad';

const Tab = `${({ active }) => active && `border-bottom: 2px solid black; opacity: 1;`}`;

const categories = ['Cash', 'Credit Card', 'Bitcoin', 'Bitcoin2'];


function DashboardPage() {
  const [active, setActive] = useState(categories[0]);

  function selectTab({ type }: { type: string }) {
    setActive(type);
    console.log(type);
  }


  return (
    <>
      <div className="d-flex overflow-scroll p-2 z-10">
        {categories.map((type) => (
          <div className={active === type ? "tab-item active-tab" : "tab-item"}
               key={type}
               onClick={() => selectTab({ type: type })}
          >
            {type}
          </div>
        ))}
      </div>
      <div className="tab-contents">
        {categories.map((type) => (
          <div className={active === type ? "tab-content active-content" :"tab-content"}>
            <Card title="test" description={type}
                  image="https://opelmobile.com.au/wp-content/uploads/2023/06/FP6-1.png" />

            <Card title="test" description="Test"
                  image="https://opelmobile.com.au/wp-content/uploads/2023/06/FP6-1.png" />

            <Card title="test" description="Test"
                  image="https://opelmobile.com.au/wp-content/uploads/2023/06/FP6-1.png" />

            <Card title="test" description="Test"
                  image="https://opelmobile.com.au/wp-content/uploads/2023/06/FP6-1.png" />
          </div>
        ))}
      </div>
    </>
  );
}

/**
 * Persistent Layout (Inertia.js)
 *
 * [Learn more](https://inertiajs.com/pages#persistent-layouts)
 */
DashboardPage.layout = (page: React.ReactNode) => (
  <MiniAppLayout title="Mini App" children={page} />
);

export default DashboardPage;
