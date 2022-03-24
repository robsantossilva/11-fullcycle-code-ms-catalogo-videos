import * as React from "react";
import {
    Card,
    CardContent,
    Divider,
    ExpansionPanel,
    ExpansionPanelDetails,
    ExpansionPanelSummary,
    Grid,
    List,
    makeStyles,
    Theme,
    Typography,
} from "@material-ui/core";
import ExpandMoreIcon from '@material-ui/icons/ExpandMore';
import {Page} from "../../components/Page";
import UploadItem from "./UploadItem";
import { useSelector } from "react-redux";
import { Upload, UploadModule } from "../../store/upload/types";
import { VideoFileFieldsMap } from "../../util/models";

const useStyles = makeStyles((theme: Theme) => {
    return ({
        panelSummary: {
            backgroundColor: theme.palette.primary.main,
            color: theme.palette.primary.contrastText
        },
        expandedIcon: {
            color: theme.palette.primary.contrastText
        }
    })
});

const Uploads = () => {
    const classes = useStyles();

    const uploads = useSelector<UploadModule, Upload[]>(
        (state) => state.upload.uploads
    );

    //const dispatch = useDispatch();

    // React.useMemo(() => {
    //     setTimeout(()=>{
    //         const obj: any = {
    //             video: {
    //                 id: 'd40617ac-b4ec-4592-a5d7-37ba21cb1344',
    //                 title: 'E o vento levou'
    //             },
    //             files: [
    //                 {
    //                     file: new File([""], "trailer_file.mp4"),
    //                     fileField: "trailer_file"
    //                 },
    //                 {
    //                     file: new File([""], "video_file.mp4"),
    //                     fileField: "video_file"
    //                 }
    //             ]
    //         }
    //         dispatch(Creators.addUpload(obj));
    //         // const progress1 = {
    //         //     fileField: 'trailer_file',
    //         //     progress: 10,
    //         //     video: {id: 'd40617ac-b4ec-4592-a5d7-37ba21cb1344'}
    //         // } as any;
    //         // dispatch(Creators.updateProgress(progress1));
    
    //         // const progress2 = {
    //         //     fileField: 'video_file',
    //         //     progress: 20,
    //         //     video: {id: 'd40617ac-b4ec-4592-a5d7-37ba21cb1344'}
    //         // } as any;
    //         // dispatch(Creators.updateProgress(progress2));
    
    //     }, 1000);
    // }, [true]);

    return (
        <Page title={'Uploads'}>
            {
                uploads.map((upload, key) => (
                    <Card elevation={5} key={key}>
                        <CardContent>
                            <UploadItem 
                                uploadOrFile={upload}
                            >
                                {upload.video.title}
                            </UploadItem>
                            <ExpansionPanel style={{margin: 0}}>
                                <ExpansionPanelSummary
                                    className={classes.panelSummary}
                                    expandIcon={<ExpandMoreIcon className={classes.expandedIcon}/>}
                                >
                                    <Typography>Ver detalhes</Typography>
                                </ExpansionPanelSummary>
                                <ExpansionPanelDetails style={{padding: '0px'}}>
                                    <Grid item xs={12}>
                                        <List dense={true} style={{padding: '0px'}}>
                                            {
                                                upload.files.map((file, key) => (
                                                    <React.Fragment key={key}>
                                                        <Divider/>
                                                        <UploadItem uploadOrFile={file}>
                                                            {`${VideoFileFieldsMap[file.fileField]} - ${file.filename}`}
                                                        </UploadItem>
                                                    </React.Fragment>
                                                ))
                                            }
                                        </List>
                                    </Grid>
                                </ExpansionPanelDetails>
                            </ExpansionPanel>
                        </CardContent>
                    </Card>
                ))
            }
        </Page>
    );
};

export default Uploads;