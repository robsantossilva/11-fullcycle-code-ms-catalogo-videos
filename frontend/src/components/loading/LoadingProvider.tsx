// @flow
import * as React from 'react';
import LoadingContext from "./LoadingContext";
import {useEffect, useMemo, useState} from "react";
import axios, { AxiosRequestConfig } from  'axios';
import { addGlobalRequestInterceptor, addGlobalResponseInterceptor, removeGlobalRequestInterceptor, removeGlobalResponseInterceptor } from '../../util/http';

export const LoadingProvider = (props) => {
    const [loading, setLoading] = useState<boolean>(false);
    const [countRequest, setCountRequest] = useState(0);   

    //useMemo vs useCallback
    useMemo(() => {
        let isSubscribed = true;

        const requestIds = addGlobalRequestInterceptor((config) => {
            if(isSubscribed && ignoreLoading(config)){
                setLoading(true);
                setCountRequest((prevCountRequest) => prevCountRequest + 1)
            }
            return config
        });

        const responseIds = addGlobalResponseInterceptor((response) => {
            if(isSubscribed && ignoreLoading(response.config)){
                decrementCountRequest();
            }
            return response
            }, 
            (error) => {
                if(isSubscribed && ignoreLoading(error.config)){
                    decrementCountRequest();
                }
                return Promise.reject(error);
            }
        );

        return () => {
            isSubscribed = false;
            removeGlobalRequestInterceptor(requestIds);
            removeGlobalResponseInterceptor(responseIds);
        }
    }, [true]);   

    useEffect(() => {
        if (!countRequest) {
            setLoading(false);
        }
    }, [countRequest]);

    function decrementCountRequest() {
        setCountRequest((prevCountRequest) => prevCountRequest - 1);
    }

    function ignoreLoading(config: AxiosRequestConfig){
        return (!config.headers || !config.headers.hasOwnProperty('x-ignore-loading'))
    }

    return (
        <LoadingContext.Provider value={loading}>
            {props.children}
        </LoadingContext.Provider>
    );
};