import * as React from 'react';
import { useParams } from 'react-router-dom';
import { Page } from "../../components/Page";
import { Form } from './Form';

const PageForm = () => {
    const {id} = useParams<{id}>();
    return (
        <Page title={!id  ? 'Create genre' : 'Edit genre'}>
            <Form id={id || ""} />
        </Page>
    );
}

export default PageForm;