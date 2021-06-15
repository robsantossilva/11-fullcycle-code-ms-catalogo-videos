import * as React from 'react';
import { Box, Fab } from '@material-ui/core';
import { Link } from 'react-router-dom';
import { Page } from '../../components/Page';
import AddIcon from '@material-ui/icons/Add';
import Table from './Table';

interface ListProps {

}

const PageList = (props: ListProps) => {
    return(
        <Page title="List Categories">
            <Box dir={'rtl'}>
                <Fab
                    title="Add Category"
                    size="small"
                    component={Link}
                    to="/categories/create"
                >
                    <AddIcon />
                </Fab>
            </Box>
            <Box>
                <Table />
            </Box>
        </Page>
    );
}

export default PageList;