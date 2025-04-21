import { Config } from 'ziggy-js';

export interface User {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  owner: string;
  photo: string;
  deleted_at: string;
  account: Account;
}

export interface Employee {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  owner: string;
  photo: string;
  password: string;
  deleted_at: string;
  account: Account;
}

export interface Bot {
  id: number;
  name: string;
  token: string;
}

export interface Shop {
  id: number;
  bot: Bot;
  account: Account;
  name: string;
  currency: string;
  slug: string;
  logo: string;
  categories: Category[]
}

export interface Category {
  id: number;
  name: string;
  description: string;
  parent_id: number;
  image: string;
  products: Product[];
  shop: Shop;
  shop_id: number;
}

export interface Product {
  id: number;
  category_id: number;
  shop_id: number;
  name: string;
  description: string;
  short_description: string;
  slug: string;
  price: number;
  discount_price: number;
  image: string;
  category: Category;
  shop: Shop;
}

export interface Account {
  id: number;
  name: string;
  users: User[];
  contacts: Contact[];
  connections: Connection[];
  organizations: Organization[];
  messenger: Messenger[];
}

export interface Contact {
  id: number;
  name: string;
  first_name: string;
  last_name: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  region: string;
  country: string;
  postal_code: string;
  deleted_at: string;
  organization_id: number;
  organization: Organization;
}
export interface Connection {
  phone: string;
  id: number;
  created_at: string;
}

export interface Messenger {
  id: number;
  name: string;
  deleted_at: string;
}

export interface Organization {
  id: number;
  name: string;
  email: string;
  phone: string;
  address: string;
  city: string;
  region: string;
  country: string;
  postal_code: string;
  deleted_at: string;
  contacts: Contact[];
}

export interface Role {
  id: number;
  name: string;
}

export type PaginatedData<T> = {
  data: T[];
  links: {
    first: string;
    last: string;
    prev: string | null;
    next: string | null;
  };

  meta: {
    current_page: number;
    from: number;
    last_page: number;
    path: string;
    per_page: number;
    to: number;
    total: number;

    links: {
      url: null | string;
      label: string;
      active: boolean;
    }[];
  };
};

export type PageProps<
  T extends Record<string, unknown> = Record<string, unknown>
> = T & {
  auth: {
    user: User;
  };
  flash: {
    success: string | null;
    error: string | null;
  };
  ziggy: Config & { location: string };
};
