import csCheckoutModalCommon from "./Common";

const Screen9 = (props) => {
    const closeModal = () => {
        csCheckoutModalCommon.hideModal();
    }

    return (
        <form className='cs-checkout-groups-screen-7'>
            <h4>You're ready</h4>

            {props.accountUpdated ? (
                <p>
                    We have saved your Management account preferences.
                </p>
            ) : null}

            <p>
                You can now click the 'close' button below to close this window and
                continue checkout, or change the Manager if you wish.
            </p>

            <div className="cs-modal-button-actions">
                <button
                    className="btn btn-prev"
                    onClick={props.updateScreenNumber(1)}
                >Change Manager
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

export default Screen9
