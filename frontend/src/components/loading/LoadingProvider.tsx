// @flow
import * as React from 'react';
import LoadingContext from "./LoadingContext";
import {useEffect, useMemo, useState} from "react";
import axios from  'axios';
import { addGlobalRequestInterceptor, addGlobalResponseInterceptor, removeGlobalRequestInterceptor, removeGlobalResponseInterceptor } from '../../util/http';

export const LoadingProvider = (props) => {
    const [loading, setLoading] = useState<boolean>(false);
    const [countRequest, setCountRequest] = useState(0);   

    useMemo(() => {
        let isSubscribed = true;

        const requestIds = addGlobalRequestInterceptor((config) => {
            isSubscribed && setLoading(true);
            return config
        });

        const responseIds = addGlobalResponseInterceptor((response) => {
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
            removeGlobalRequestInterceptor(requestIds);
            removeGlobalResponseInterceptor(responseIds);
        }
    }, [true]);   

    return (
        <LoadingContext.Provider value={loading}>
            {props.children}
        </LoadingContext.Provider>
    );
};