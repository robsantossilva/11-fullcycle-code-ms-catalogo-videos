import * as React from 'react';
import { useParams } from 'react-router-dom';
import { Page } from "../../components/Page";
import { Form } from './Form';

const PageForm = () => {
    const {id}  = useParams<{id:string}>();
    return (
        <Page title={!id ? 'Create cast member' :'Edit cast member'}>
            <Form id={id} />
        </Page>
    );
}

export default PageForm;