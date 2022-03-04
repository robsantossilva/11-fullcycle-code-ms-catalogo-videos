import * as React from 'react';
import { Card, CardContent, Checkbox, FormControlLabel, FormHelperText, Grid, makeStyles, TextField, Theme, Typography, useMediaQuery, useTheme } from '@material-ui/core';
import { useForm } from 'react-hook-form';
import videoHttp from '../../../util/http/video-http';
import { createRef, MutableRefObject, useContext, useEffect, useMemo, useRef } from 'react';
import { useHistory } from 'react-router-dom';
import * as yup from '../../../util/vendor/yup';
import { useState } from 'react';
import { useSnackbar } from 'notistack';
import SubmitActions from '../../../components/SubmitActions';
import { DefaultForm } from '../../../components/DefaultForm';
import { Video, VideoFileFieldsMap } from '../../../util/models';
import UploadField from './UploadField';
import RatingField from './RatingField';
import GenreField, { GenreFieldComponent } from './GenreField';
import CategoryField, { CategoryFieldComponent } from './CategoryField';
import CastMemberField, { CastMemberFieldComponent } from './CastMemberField';
import { omit, zipObject } from 'lodash';
import { InputFileComponent } from '../../../components/InputFile';
import useSnackbarFormError from '../../../hooks/useSnackbarFormError';
import LoadingContext from '../../../components/loading/LoadingContext';
import SnackbarUpload from '../../../components/SnackbarUpload';
import { useDispatch, useSelector } from 'react-redux';
import { State as UploadState, Upload } from "../../../store/upload/types";
import { Creators } from '../../../store/upload';

const useStyles = makeStyles((theme: Theme) => ({
    cardUpload: {
        borderRadius:"4px",
        backgroundColor:"#f5f5f5",
        margin: theme.spacing(2, 0)
    },
    cardOpened: {
        borderRadius: "4px",
        backgroundColor: "#f5f5f5",
    },
    cardContentOpened: {
        paddingBottom: theme.spacing(2) + 'px !important'
    },
}))

const validationSchema = yup.object().shape({
    title: yup.string()
        .label('Título')
        .required()
        .max(255),
    description: yup.string()
        .label('Sinopse')
        .required(),
    year_launched: yup.number()
        .label('Ano de lançamento')
        .required()
        .min(1),
    duration: yup.number()
        .label('Duração')
        .required()
        .min(1),
    rating: yup.string()
        .label('Classificação')
        .required(),
    cast_members: yup.array()
        .label('Elenco')
        .required(),
    genres: yup.array()
        .label('Gêneros')
        .required()
        .test({
            message: 'Cada gênero escolhido precisa ter pelo menos uma categoria selecionada',
            test(value) { //array genres [{name, categories: []}]
                var genres = value as any;
                return genres.every(
                    v => v.categories.filter(
                        cat => this.parent.categories.map(c => c.id).includes(cat.id)
                    ).length !== 0
                );
            }
        })
    ,categories: yup.array()
        .label('Categorias')
        .required(),
});

interface FormProps {
    id?:string
}

const fileFields = Object.keys(VideoFileFieldsMap);

export const Form: React.FC<FormProps> = ({id}) => {

    const classes = useStyles();

    const { 
        register, 
        handleSubmit, 
        getValues,
        reset,
        watch,
        setValue,
        errors,
        triggerValidation,
        formState,

    } = useForm<{
        title,
        description,
        year_launched,
        duration,
        rating,
        cast_members,
        genres,
        categories,
        opened
    }>({
        validationSchema,
        defaultValues: {
            rating: null,
            cast_members: [],
            genres: [],
            categories: [],
            opened: false,
        }
    });
    useSnackbarFormError(formState.submitCount, errors);

    const snackbar = useSnackbar();
    const history = useHistory();
    const [video, setVideo] = useState<Video | null>(null);
    const loading = useContext(LoadingContext)
    const theme = useTheme();
    const isGreaterMd = useMediaQuery(theme.breakpoints.up('md'));
    const castMemberRef = useRef() as MutableRefObject<CastMemberFieldComponent>;
    const genreRef = useRef() as MutableRefObject<GenreFieldComponent>;
    const categoryRef = useRef() as MutableRefObject<CategoryFieldComponent>;
    const uploadsRef = useRef(
        zipObject(fileFields, fileFields.map(() => createRef()))
    ) as MutableRefObject<{ [key: string]: MutableRefObject<InputFileComponent> }>;

    const uploads = useSelector<{upload: UploadState}, Upload[]>(
        (state)=> state.upload.uploads
    );

    console.log('uploads',uploads);

    const dispatch = useDispatch();

    useMemo(() => {
        setTimeout(()=>{
            const obj: any = {
                video: {
                    id: '1',
                    title: 'E o vento levou'
                },
                files: [
                    {
                        file: new File([""], "trailer_file.mp4"),
                        fileField: "trailer_file"
                    },
                    {
                        file: new File([""], "video_file.mp4"),
                        fileField: "video_file"
                    }
                ]
            }
            dispatch(Creators.addUpload(obj));
            const progress1 = {
                fileField: 'trailer_file',
                progress: 10,
                video: {id: '1'}
            } as any;
            dispatch(Creators.updateProgress(progress1));
    
            const progress2 = {
                fileField: 'video_file',
                progress: 20,
                video: {id: '1'}
            } as any;
            dispatch(Creators.updateProgress(progress2));
    
        }, 1000);
    }, [true]);

    useEffect(() => {
        [
            'rating',
            'opened',
            'cast_members',
            'genres',
            'categories',
            ...fileFields
        ].forEach(name => register({name}));
    }, [register]);

    useEffect(() => {

        snackbar.enqueueSnackbar('', {
            key: 'snackbar-upload',
            persist: true,
            anchorOrigin: {
                vertical: 'bottom',
                horizontal: 'right'
            },
            content: (key, message) => (
                <SnackbarUpload id={key}/>
            )
        });

        if(!id){
            return;
        }

        let isSubscribed = true;
        
        (async function getData() {
            try {
                const {data} = await videoHttp.get(id);
                if(isSubscribed){
                    setVideo(data.data);
                    reset(data.data);
                }
            } catch (error) {
                console.log(error);
                snackbar.enqueueSnackbar(
                    'Error trying to load video',
                    {variant: 'error',}
                )
            }
        })();
        return () => {
            isSubscribed = false;
        }
    }, []);

    async function onSubmit(formData, event) {
        const sendData = omit(
            formData,
            [...fileFields, 'cast_members', 'genres', 'categories']
        );
        sendData['cast_members_id'] = formData['cast_members'].map(cast_member => cast_member.id);
        sendData['categories_id'] = formData['categories'].map(category => category.id);
        sendData['genres_id'] = formData['genres'].map(genre => genre.id);

        try {
            const http = !video
            ? videoHttp.create(sendData)
            : videoHttp.update(video.id, sendData);

            const {data} = await http;

            snackbar.enqueueSnackbar(
                'Video saved successfully',
                {variant:"success"}
            );
            id && resetForm(video);
            setTimeout(() => {
                if(event){
                    id
                        ? history.replace(`/videos/${data.data.id}/edit`)
                        : history.push(`/videos/${data.data.id}/edit`);
                }else{
                    history.push('/videos')
                }
            });
          
        } catch (error) {
            console.error(error);
            snackbar.enqueueSnackbar(
                'Error trying to save video',
                {variant:"error"}
            );
        }
    }

    function resetForm(data) {
        Object.keys(uploadsRef.current).forEach(
            field => uploadsRef.current[field].current.clear()
        );
        castMemberRef.current.clear();
        genreRef.current.clear();
        categoryRef.current.clear();
        //reset(data);
    }

    return (
        <DefaultForm 
            GridItemProps={{xs:12}} 
            onSubmit={handleSubmit(onSubmit)}
        >
            <Grid container spacing={5}>
                <Grid item xs={12} md={6}>


                    <TextField
                        name="title"
                        label="Título"
                        variant={'outlined'}
                        fullWidth
                        inputRef={register}
                        disabled={loading}
                        InputLabelProps={{shrink: true}}
                        error={errors.title !== undefined}
                        helperText={errors.title && errors.title.message}
                    />
                    <TextField
                        name="description"
                        label="Sinopse"
                        multiline
                        rows="4"
                        margin="normal"
                        variant="outlined"
                        fullWidth
                        inputRef={register}
                        disabled={loading}
                        InputLabelProps={{shrink: true}}
                        error={errors.description !== undefined}
                        helperText={errors.description && errors.description.message}
                    />
                    <Grid container spacing={1}>
                        <Grid item xs={6}>
                            <TextField
                                name="year_launched"
                                label="Ano de lançamento"
                                type="number"
                                margin="normal"
                                variant="outlined"
                                fullWidth
                                inputRef={register}
                                disabled={loading}
                                InputLabelProps={{shrink: true}}
                                error={errors.year_launched !== undefined}
                                helperText={errors.year_launched && errors.year_launched.message}
                            />
                        </Grid>
                        <Grid item xs={6}>
                            <TextField
                                name="duration"
                                label="Duração"
                                type="number"
                                margin="normal"
                                variant="outlined"
                                fullWidth
                                inputRef={register}
                                disabled={loading}
                                InputLabelProps={{shrink: true}}
                                error={errors.duration !== undefined}
                                helperText={errors.duration && errors.duration.message}
                            />
                        </Grid>
                    </Grid>
                    
                    <Grid container>
                        <Grid item xs={12}>
                            <CastMemberField 
                                ref={castMemberRef}
                                castMembers={watch('cast_members')} 
                                setCastMembers={(value) => setValue('cast_members', value, true)} 
                                error={errors.cast_members}
                                disabled={loading}
                            />
                        </Grid>
                    </Grid>

                    <Grid container spacing={2}>
                        <Grid item xs={12} md={6}>
                            <GenreField 
                                ref={genreRef}
                                genres={watch('genres')}
                                setGenres={(value) => setValue('genres', value, true)}
                                categories={watch('categories')}
                                setCategories={(value) => setValue('categories', value, true)}
                                error={errors.genres}
                                disabled={loading}
                            />
                        </Grid>
                        <Grid item xs={12} md={6}>
                            <CategoryField 
                                ref={categoryRef}
                                categories={watch('categories')}
                                setCategories={(value) => setValue('categories', value, true)}
                                genres={watch('genres')}
                                error={errors.categories}
                                disabled={loading}
                            />
                        </Grid>
                        <Grid item xs={12}>
                            <FormHelperText>
                                Escolha os gêneros do vídeo
                            </FormHelperText>
                            <FormHelperText>
                                Escolha pelo menos uma categoria de cada gênero
                            </FormHelperText>
                        </Grid>
                    </Grid>
                    
                </Grid>

                <Grid item xs={12} md={6}>
                    <RatingField  
                        value={watch('rating')}
                        setValue={(value) => setValue('rating', value, true)}
                        error={errors.rating}
                        disabled={loading}
                        FormControlProps={{
                            margin: isGreaterMd ? 'none' : 'normal'
                        }}
                    />
                    <br/>
                    <Card className={classes.cardUpload}>
                        <CardContent>
                            <Typography color="primary" variant="h6">
                                Imagens
                            </Typography>
                            <UploadField
                                ref={uploadsRef.current['thumb_file']}
                                accept={'image/*'}
                                label={'Thumb'}
                                setValue={(value) => setValue('thumb_file', value)}
                            />
                            <UploadField 
                                ref={uploadsRef.current['banner_file']}
                                accept={'image/*'}
                                label={'Banner'}
                                setValue={(value) => setValue('banner_file', value)}
                            />
                        </CardContent>
                    </Card>
                    <Card className={classes.cardUpload}>
                        <CardContent>
                            <Typography color="primary" variant="h6">
                                Videos
                            </Typography>
                            <UploadField 
                                ref={uploadsRef.current['trailer_file']}
                                accept={'video/mp4'}
                                label={'Trailer'}
                                setValue={(value) => setValue('trailer_file', value)}
                            />
                            <UploadField
                                ref={uploadsRef.current['video_file']}
                                accept={'video/mp4'}
                                label={'Principal'}
                                setValue={(value) => setValue('video_file', value)}
                            />
                        </CardContent>
                    </Card>
                    
                    <Card className={classes.cardUpload}>
                        <CardContent className={classes.cardContentOpened}>
                            <FormControlLabel
                                control={
                                    <Checkbox
                                        name="opened"
                                        color={'primary'}
                                        onChange={
                                            () => setValue('opened', !getValues()['opened'])
                                        }
                                        checked={watch('opened')}
                                        disabled={loading}
                                    />
                                }
                                label={
                                    <Typography color="primary" variant={"subtitle2"}>
                                        Quero que este conteúdo apareça na seção lançamentos
                                    </Typography>
                                }
                                labelPlacement="end"
                            />
                        </CardContent>
                    </Card>
                </Grid>
            </Grid>

            <SubmitActions 
                
                disabledButtons={loading} 
                handleSave={() => 
                    triggerValidation().then(isValid => {
                        isValid && onSubmit(getValues(), null)
                    })  
                }
            />
        </DefaultForm>
    );
}