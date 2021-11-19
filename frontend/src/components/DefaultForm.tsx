import * as React from 'react';
import { Grid, GridProps, Theme } from '@material-ui/core';
import { makeStyles } from '@material-ui/styles';

const useStyles = makeStyles((theme: Theme) => {
    return {
        gridItem: {
            padding: theme.spacing(1, 0)
        }
    }
});

interface DefaultFormProps extends React.DetailedHTMLProps<React.FormHTMLAttributes<HTMLFormElement>, HTMLFormElement> {
    GridItemProps?: GridProps
    GridContainerProps?: GridProps
}

export const DefaultForm: React.FC<DefaultFormProps> = (props) => {

    const classes = useStyles();
    const { GridContainerProps, GridItemProps, ...other } = props;

    return (
        <form {...other}>
            <Grid container {...GridContainerProps}>
                <Grid className={classes.gridItem} item {...GridItemProps}>
                    {props.children}
                </Grid>
            </Grid>
        </form>
    );
};