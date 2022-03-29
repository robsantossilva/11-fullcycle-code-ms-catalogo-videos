import * as React from 'react';
import { useCallback, useContext, useEffect, useRef, useState } from 'react';
import genreHttp from "../../util/http/genre-http";
import { Chip, IconButton, MuiThemeProvider } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { Genre, ListResponse } from '../../util/models';
import DefaultTable, { makeActionStyles, MuiDataTableRefComponent, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';
import * as yup from '../../util/vendor/yup';
import useFilter from '../../hooks/useFilter';
import { FilterResetButton } from '../../components/Table/FilterResetButton';
import categoryHttp from '../../util/http/category-http';
import LoadingContext from '../../components/loading/LoadingContext';

interface Category {
    name: string
}

const columnsDefinition: TableColumn[] = [
    {
        name: "id",
        label: "ID",
        width: '30%',
        options: {
            sort: false,
            filter: false
        }
    },
    {
        name: "name",
        label: "Name",
        width: "23%",
        options: {
            filter: false
        }
    },
    {
        name: "is_active",
        label: "Is Active",
        options: {
            customBodyRender(value, tableMeta, updateValue){
                return value ? <Chip label="Active" color="primary" /> : <Chip label="Inactive" color="secondary" />
            }
        },
        width: '4%',
    },
    {
        name: "categories",
        label: "Categories",
        width: '20%',
        options: {
            filterType: 'multiselect',
            filterOptions: {
                names: []
            },
            customBodyRender(value: Category[], tableMeta, updateValue){
                const categories = value.map((e:Category, i:Number) => e.name).join(', ');
                return categories;
            }
        }
    },
    {
        name: "created_at",
        label: "Created At",
        width: '10%',
        options: {
            filter: false,
            customBodyRender(value, tableMeta, updateValue){
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        }
    },
    {
        name: "actions",
        label: "Ações",
        options: {
            sort: false,
            filter: false,
            customBodyRender: (value, tableMeta) => {
                return (
                    <IconButton
                        color={'secondary'}
                        component={Link}
                        to={`/genres/${tableMeta.rowData[0]}/edit`}
                    >
                        <EditIcon/>
                    </IconButton>
                )
            }
        }
    }
];

const debounceTime = 300;
const debouncedSearchTime = 300;
const rowsPerPage = 15;
const rowsPerPageOptions = [15, 25, 50];
const extraFilter = {
    createValidationSchema: () => {
        return yup.object().shape({
            categories: yup.mixed()
                .nullable()
                .transform(value => {
                    return !value || value === '' ? undefined : value.split(',');
                })
                .default(null),
        })
    },
    formatSearchParams: (debouncedState) => {
        return debouncedState.extraFilter ? {
            ...(
                debouncedState.extraFilter.categories &&
                {categories: debouncedState.extraFilter.categories.join(',')}
            )
        } : undefined
    },
    getStateFromURL: (queryParams) => {
        return {
            categories: queryParams.get('categories')
        }
    }
}

const Table = () => {

    const {enqueueSnackbar} = useSnackbar();
    const subscribed = useRef(true)
    const [data, setData] = useState<Genre[]>([]);
    const loading = useContext(LoadingContext)
    const tableRef = useRef() as React.MutableRefObject<MuiDataTableRefComponent>;

    const {
        columns,
        filterManager,
        filterState,
        debouncedFilterState,
        totalRecords,
        setTotalRecords,
        cleanSearchText
      } = useFilter({
        columns: columnsDefinition,
        debounceTime: debounceTime,
        rowsPerPage,
        rowsPerPageOptions,
        tableRef,
        extraFilter,
      });

    const searchText = cleanSearchText(debouncedFilterState.search);
    const indexColumnCategories = columns.findIndex(c => c.name === 'categories');
    const columnCategories = columns[indexColumnCategories];
    const categoriesFilterValue = filterState.extraFilter && filterState.extraFilter.categories;
    (columnCategories.options as any).filterList = categoriesFilterValue ? categoriesFilterValue : [];
    
    // const serverSideFilterList = columns.map(column => []);
    // if (categoriesFilterValue) {
    //     serverSideFilterList[indexColumnCategories] = categoriesFilterValue;
    // }

    useEffect(() => {
        let isSubscribed = true;
        (async () => {
            try {
                const {data} = await categoryHttp.list({queryParams: {all: ''}});
                if (isSubscribed) {
                    (columnCategories.options as any).filterOptions.names = data.data.map(category => category.name)
                }
            } catch (error) {
                console.error(error);
                enqueueSnackbar(
                    'Não foi possível carregar as informações',
                    {variant: 'error',}
                )
            }
        })();

        return () => {
            isSubscribed = false;
        }
    }, [columnCategories.options, enqueueSnackbar]);


    const getData = useCallback(async ({
        search,
        page,
        per_page,
        sort,
        dir,
        categories
    }) => {
        try {
            const {data} = await genreHttp.list<ListResponse<Genre>>({
                queryParams: {
                    search,
                    page,
                    per_page,
                    sort,
                    dir,
                    ...(categories &&
                        {categories: categories.join(',')}
                    )
                }
            });
            if (subscribed.current) {
                setData(data.data);
                setTotalRecords(data.meta.total);
            }
        } catch (error) {
            console.error(error);

            if(genreHttp.isCancelledRequest(error)){
                return;
            }
            
            enqueueSnackbar(
                'Error trying to list genres',
                {variant:"error"}
            );
        }
    }, [enqueueSnackbar, setTotalRecords])


    useEffect(() => {
        subscribed.current = true;
        getData({
            search: searchText,
            page: debouncedFilterState.pagination.page,
            per_page: debouncedFilterState.pagination.per_page,
            sort: debouncedFilterState.order.sort,
            dir: debouncedFilterState.order.dir,
            ...(
                debouncedFilterState.extraFilter &&
                debouncedFilterState.extraFilter.categories &&
                {categories: debouncedFilterState.extraFilter.categories.join(',')}
            )
        });
        return () => {
            subscribed.current = false;
        }
    }, [
        debouncedFilterState.extraFilter, 
        debouncedFilterState.order.dir, 
        debouncedFilterState.order.sort, 
        debouncedFilterState.pagination.page, 
        debouncedFilterState.pagination.per_page, 
        getData, 
        searchText
    ]);

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length -1)}>
            <DefaultTable 
                title="Genre List"
                columns={columns} 
                data={data}
                loading={loading}
                debouncedSearchTime={debouncedSearchTime}
                ref={tableRef}
                options={{
                    serverSide: true,
                    searchText: filterState.search as any,
                    page: filterState.pagination.page - 1,
                    rowsPerPage: filterState.pagination.per_page,
                    rowsPerPageOptions,
                    count: totalRecords,
                    onFilterChange: (column, filterList, type) => {
                        const columnIndex = columns.findIndex(c => c.name === column);
                        console.log(filterList);
                        filterManager.changeExtraFilter({
                            [column as any]: filterList[columnIndex].length ? filterList[columnIndex] : null
                        })
                    },
                    customToolbar: () => (
                        <FilterResetButton 
                            handleClick={ () => filterManager.resetFilter() }
                        />
                    ),
                    onSearchChange: (value) => filterManager.changeSearch(value),
                    onChangePage: (page) => filterManager.changePage(page),
                    onChangeRowsPerPage: (perPage) => filterManager.changeRowsPerPage(perPage),
                    onColumnSortChange: (changedColumn: string, direction: string) =>
                        filterManager.changeColumnSort(changedColumn, direction)
                }}
            />
        </MuiThemeProvider>
    );
}

export default Table;