import * as React from 'react';
import { useEffect, useState, useRef, useContext, useCallback } from 'react';
import {IconButton, MuiThemeProvider } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import categoryHttp from '../../util/http/category-http';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { BadgeNo, BadgeYes } from '../../components/Badge';
import { Category, ListResponse } from '../../util/models';
import DefaultTable, { makeActionStyles, MuiDataTableRefComponent, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';
import { FilterResetButton } from '../../components/Table/FilterResetButton';
import useFilter from '../../hooks/useFilter';
import LoadingContext from '../../components/loading/LoadingContext';

const columnsDefinition: TableColumn[] = [
    {
        name: 'id',
        label: 'ID',
        width: '30%',
        options: {
            sort: false,
            filter: false
        }
    },
    {
        name: "name",
        label: "Name",
        width: '43%',
        options: {
            filter: false
        }
    },
    {
        name: "is_active",
        label: "Active?",
        options: {
            filterOptions: {
                names: ['Yes', 'No']
            },
            customBodyRender(value, tableMeta, updateValue){
                return value ? <BadgeYes label={"Yes"}/> : <BadgeNo label={"No"} />
            }
        },
        width: '4%'
    },
    {
        name: "created_at",
        label: "Created At",
        options: {
            filter: false,
            customBodyRender(value, tableMeta, updateValue){
                return <span>{format(parseISO(value), 'dd/MM/yyyy')}</span>
            }
        },
        width: '10%'
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
                        to={`/categories/${tableMeta.rowData[0]}/edit`}
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
const Table: React.FC = () => {

    const {enqueueSnackbar} = useSnackbar(); 
    const subscribed = useRef(true)
    const [data, setData] = useState<Category[]>([]);
    const loading = useContext(LoadingContext);
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
    });

    const searchText = cleanSearchText(debouncedFilterState.search);

    const getData = useCallback(async ({
        search, 
        page, 
        per_page, 
        sort,
        dir
    }) => {
        try{
            const {data} = await categoryHttp.list<ListResponse<Category>>({
                queryParams: {
                    search,
                    page,
                    per_page,
                    sort,
                    dir
                }
            });
            if(subscribed.current){
                setData(data.data);
                setTotalRecords(data.meta.total);
            }
        } catch (error) {
            console.error(error);
            
            if(categoryHttp.isCancelledRequest(error)){
                return;
            }

            enqueueSnackbar(
                'Error trying to list categories',
                {variant:"error"}
            );
        }
    }, [enqueueSnackbar, setTotalRecords]);

    useEffect(() => {
        subscribed.current = true;
        getData({
            search: searchText,
            page: debouncedFilterState.pagination.page,
            per_page: debouncedFilterState.pagination.per_page,
            sort: debouncedFilterState.order.sort,
            dir: debouncedFilterState.order.dir
        });
        return () => {
            subscribed.current = false;
        }
    },[
        debouncedFilterState.order.dir, 
        debouncedFilterState.order.sort, 
        debouncedFilterState.pagination.page, 
        debouncedFilterState.pagination.per_page, 
        getData, 
        searchText]);

    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length -1)}>
            <DefaultTable 
                title="Category List"
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