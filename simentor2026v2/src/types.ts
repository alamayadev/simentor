import { LucideIcon } from 'lucide-react';

export interface SubMenu {
  name: string;
  path: string;
  icon?: LucideIcon;
  dropdown?: SubMenu[];
}

export interface ModuleMenu {
  id: string;
  name: string;
  path: string;
  subMenus: SubMenu[];
}
