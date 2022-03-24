import { END, eventChannel } from 'redux-saga';
import {actionChannel, call, put, take} from 'redux-saga/effects';
import videoHttp from '../../util/http/video-http';
import { Video } from '../../util/models';
import {Types, Creators} from './index';
import {AddUploadAction, FileInfo} from './types';

export function* uploadWatcherSaga() {
    const newFilesChannel = yield actionChannel(Types.ADD_UPLOAD);

    while(true){
        const {payload}: AddUploadAction = yield take(newFilesChannel);// [ [], [] ]
        for(const fileInfo of payload.files){
            try {
                const response = yield call(uploadFile, {video: payload.video, fileInfo})
                console.log('uploadWatcherSaga response', response)
            } catch (e) {
                console.log('Error uploadWatcherSaga', e)
            }
            
        }
        console.log('uploadWatcherSaga', payload);
    }
}

//criando um novo video
//1 - POST e criar
//2 - PUT com upload

//editar um novo video
//1 - PUT e editar
//2 - PUT com upload
function* uploadFile({video, fileInfo}: { video: Video, fileInfo: FileInfo }) {
    const channel = yield call(sendUpload, {id: video.id, fileInfo});

    while(true){
        try {
            const {progress, response} = yield take(channel);
            console.log('response', fileInfo.fileField, response );
            if(response){
                return response;
            }
            console.log('progress',fileInfo.fileField, progress);
            console.log('updateProgress', fileInfo.fileField)
            yield put(Creators.updateProgress({
                video,
                fileField: fileInfo.fileField,
                progress
            }));
        }catch (e) {
            console.log('Error', fileInfo.fileField, e)
            yield put(Creators.setUploadError({
                video,
                fileField: fileInfo.fileField,
                error: e
            }));
            throw e;
        }
        
    }
}

function sendUpload({id, fileInfo}: {id: string, fileInfo: FileInfo}) {
    
    return eventChannel( (emitter) => {
        videoHttp.partialUpdate(
            id, {
                _method: 'PATCH',
                [fileInfo.fileField]: fileInfo.file
            }, 
            {
                http: {
                    usePost: true
                },
                config: {
                    headers: {
                        'x-ignore-loading': true
                    },
                    onUploadProgress(progressEvent: ProgressEvent) {
                        if(progressEvent.lengthComputable) {
                            console.log('>>>>',progressEvent)
                            const progress = progressEvent.loaded / progressEvent.total;
                            emitter({progress});
                        }
                    }
                }
            }
        )
        .then(response => emitter({response}))
        .catch(error => emitter(error))
        .finally(() => emitter(END));

        const unsubscribe = () => {}
        return unsubscribe;
    })
    
}