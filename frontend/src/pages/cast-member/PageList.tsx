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
        <Page title="Cast Members List">
            <Box dir={'rtl'}>
                <Fab
                    title="Add Cast Member"
                    size="small"
                    component={Link}
                    to="/cast-members/create"
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