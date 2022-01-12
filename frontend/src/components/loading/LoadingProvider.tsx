// @flow
import * as React from 'react';
import LoadingContext from "./LoadingContext";
import {useEffect, useState} from "react";
import axios from  'axios';

export const LoadingProvider = (props) => {
    const [loading, setLoading] = useState<boolean>(false);    

    useEffect(() => {
        let isSubscribed = true;
        axios.interceptors.request.use((config) => {
            isSubscribed && setLoading(true);
            return config
        });

        axios.interceptors.response.use(
            (response) => {
                isSubscribed && setLoading(false);
                return response
            }, 
            (error) => {
                isSubscribed && setLoading(false);
                return Promise.reject(error);
            }
        );

        return () => {
            isSubscribed = false;
        }
    });        

    return (
        <LoadingContext.Provider value={loading}>
            {props.children}
        </LoadingContext.Provider>
    );
};