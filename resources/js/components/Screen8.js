import csCheckoutModalCommon from "./Common";

const Screen8 = (props) => {
    const closeModal = (event) => {
        event.preventDefault();
        props.updateScreenNumber(8);
        csCheckoutModalCommon.hideModal();
    }

    const previousScreen = () => {
        props.updateScreenNumber(1)
    }

    return (
        <form className='cs-checkout-groups-screen-7'>
            <h4>All done!</h4>

            {props.accountUpdated ? (
                <p>
                    We found an existing account using this email address and so have used
                    that account. Your password has not been changed.
                </p>
            ) : null}

            <p>
                You can now click the 'close' button below to close this window and then
                complete your checkout. Learners will be added to your learning management
                account.
            </p>

            <div className="cs-modal-button-actions">
                <button
                    className="btn btn-prev"
                    onClick={previousScreen}
                >
                    Start Again
                </button>

                <button
                    className="btn btn-action"
                    onClick={closeModal}
                >Close
                </button>
            </div>


        </form>
    )
}

export default Screen8
