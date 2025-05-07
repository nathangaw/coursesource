const Screen7 = (props) => {
    const searchAgain = () => {
        props.updateScreenNumber(6)
    }

    const createNewGroup = () => {
        props.updateScreenNumber(8)
    }

    return (
        <form className='cs-checkout-groups-screen-6'>
            <h4>Can't find your group?</h4>
            <p>Either search again or create a new group.</p>

            <div className="cs-modal-button-actions">
                <button onClick={searchAgain}
                        className="btn btn-prev"

                >
                    Try searching again
                </button>

                <button onClick={createNewGroup}
                        className="btn btn-action"
                >
                    Create a new group
                </button>
            </div>
        </form>
    )
}

export default Screen7
