import * as React from 'react';
import { useEffect, useState, useRef } from 'react';
import {IconButton, MuiThemeProvider } from '@material-ui/core';
import format from 'date-fns/format';
import parseISO from 'date-fns/parseISO';
import categoryHttp from '../../util/http/category-http';
import EditIcon from '@material-ui/icons/Edit';
import {Link} from "react-router-dom";
import { BadgeNo, BadgeYes } from '../../components/Badge';
import { Category, Genre, ListResponse } from '../../util/models';
import DefaultTable, { makeActionStyles, MuiDataTableRefComponent, TableColumn } from '../../components/Table';
import { useSnackbar } from 'notistack';
import { FilterResetButton } from '../../components/Table/FilterResetButton';
import { INITIAL_STATE, Creators } from '../../store/filter';
import useFilter from '../../hooks/useFilter';
import videoHttp from '../../util/http/video-http';
import DeleteDialog from '../../components/DeleteDialog';
import useDeleteCollection from '../../hooks/useDeleteCollection';

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
        name: "title",
        label: "Titulo",
        width: '43%',
        options: {
            filter: false
        }
    },
    {
        name: "genres",
        label: "Gêneros",
        width: '12%',
        options: {
            filterType: 'multiselect',
            filterOptions: {
                names: []
            },
            customBodyRender(value: Genre[], tableMeta, updateValue){
                const genres = value.map((e:Genre, i:Number) => e.name).join(', ');
                return genres;
            }
        }
    },
    {
        name: "categories",
        label: "Categories",
        width: '12%',
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
                        to={`/videos/${tableMeta.rowData[0]}/edit`}
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

    const snackbar = useSnackbar(); 
    const subscribed = useRef(true)
    const [data, setData] = useState<Category[]>([]);
    const [loading, setLoading] = useState<boolean>(false);
    const {openDeleteDialog, setOpenDeleteDialog, rowsToDelete, setRowsToDelete} = useDeleteCollection();
    const tableRef = useRef() as React.MutableRefObject<MuiDataTableRefComponent>;
    
    const {
        columns,
        filterManager,
        filterState,
        debouncedFilterState,
        dispatch,
        totalRecords, 
        setTotalRecords
    } = useFilter({
        columns: columnsDefinition,
        debounceTime: debounceTime,
        rowsPerPage,
        rowsPerPageOptions,
        tableRef,
    });

    useEffect(() => {
        subscribed.current = true;
        filterManager.pushHistory();
        getData();
        return () => {
            subscribed.current = false;
        }
    },[
        filterManager.cleanSearchText(debouncedFilterState.search),
        debouncedFilterState.pagination.page,
        debouncedFilterState.pagination.per_page,
        debouncedFilterState.order
    ]);

    async function getData() {
        setLoading(true);
        try{
            const {data} = await videoHttp.list<ListResponse<Category>>({
                queryParams: {
                    search: filterManager.cleanSearchText(debouncedFilterState.search),
                    page: debouncedFilterState.pagination.page,
                    per_page: debouncedFilterState.pagination.per_page,
                    sort: debouncedFilterState.order.sort,
                    dir: debouncedFilterState.order.dir
                }
            });
            if(subscribed.current){
                setData(data.data);
                setTotalRecords(data.meta.total);
                if(openDeleteDialog){
                    setOpenDeleteDialog(false);
                }
            }
        } catch (error) {
            console.error(error);
            
            if(categoryHttp.isCancelledRequest(error)){
                return;
            }

            snackbar.enqueueSnackbar(
                'Error trying to list categories',
                {variant:"error"}
            );
        } finally {
            setLoading(false);
        }
    }


    function deleteRows(confirmed: boolean) {
        if (!confirmed) {
            setOpenDeleteDialog(false);
            return;
        }
        const ids = rowsToDelete
            .data
            .map(value => data[value.index].id)
            .join(',');
        videoHttp
            .deleteCollection({ids})
            .then(response => {
                snackbar.enqueueSnackbar(
                    'Registros excluídos com sucesso',
                    {variant: 'success'}
                );
                if(
                    rowsToDelete.data.length === filterState.pagination.per_page
                    && filterState.pagination.page > 1
                ){
                    const page = filterState.pagination.page - 2;
                    filterManager.changePage(page);
                }else{
                    getData();
                }
            })
            .catch((error) => {
                console.error(error);
                snackbar.enqueueSnackbar(
                    'Não foi possível excluir os registros',
                    {variant: 'error',}
                )
            })
    }


    return (
        <MuiThemeProvider theme={makeActionStyles(columnsDefinition.length -1)}>
            <DeleteDialog open={openDeleteDialog} handleClose={deleteRows} />
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
                        filterManager.changeColumnSort(changedColumn, direction),
                    onRowsDelete: (rowsDeleted) => {
                        setRowsToDelete(rowsDeleted as any);
                        return false;
                    },
                }}
            />
        </MuiThemeProvider>
    );
}

export default Table;