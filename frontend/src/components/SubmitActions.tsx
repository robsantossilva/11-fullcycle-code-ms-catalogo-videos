import { Box, Button, ButtonProps, makeStyles, Theme } from "@material-ui/core";

const useStyles = makeStyles((theme: Theme) => {
    return {
        submit: {
            margin: theme.spacing(1)
        }
    }
});

interface SubmitActionsProps {
    disabledButtons?: boolean;
    handleSave: () => void
}

const SubmitActions: React.FC<SubmitActionsProps> = (props) => {

    const classes = useStyles();

    const buttonProps: ButtonProps = {
        variant: "contained",
        className: classes.submit,
        color: 'secondary',
        disabled: props.disabledButtons === undefined ? false : props.disabledButtons
    }

    return (
        <Box dir={'rtl'}>
            <Button {...buttonProps} 
                color={"primary"}
                onClick={props.handleSave}
            >
                Save
            </Button>
            <Button {...buttonProps} type="submit">Save and continue editing</Button>                
        </Box>
    );
}

export default SubmitActions;