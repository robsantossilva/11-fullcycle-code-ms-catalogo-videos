import { RouteProps } from 'react-router-dom';
import CategoryList from '../pages/category/PageList';
import MemberList from '../pages/member/PageList';
import GenreList from '../pages/genre/PageList';
import Dashboard from '../pages/Dashboard';

export interface MyRouteProps extends RouteProps {
    name: string
    label: string
}

const routes : MyRouteProps[] = [
    {
        name: 'dashboard',
        label: 'Dashboard',
        path: '/',
        component: Dashboard,
        exact: true
    },
    {
        name: 'categories.list',
        label: 'Categories',
        path: '/categories',
        component: CategoryList,
        exact: true
    },
    {
        name: 'members.list',
        label: 'Members',
        path: '/members',
        component: MemberList,
        exact: true
    },
    {
        name: 'genres.list',
        label: 'Genres',
        path: '/genres',
        component: GenreList,
        exact: true
    }
];

export default routes;