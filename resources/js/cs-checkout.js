import React, {useEffect} from 'react';
import {render, useState} from '@wordpress/element';
import csCheckoutModalCommon from './components/Common';
import FormWrapper from './components/FormWrapper';

const App = () => {
    const [display, setDisplay] = useState("block");

    const hideModal = () => {
        csCheckoutModalCommon.getModal().style.display = 'none';
    }

    const showModal = () => {
        csCheckoutModalCommon.getModal().style.display = 'block';
    }

    const containerStyle = {
        display: display,
    }

    useEffect(() => {
        let companyNameEle = document.getElementById('billing_company');
        let passwordEle = document.getElementById('account_password');
        let logged_in = csCheckoutModalCommon.logged_in;
        if( logged_in ){
            if ((companyNameEle.value.length > 3)) {
                showModal();
            }
        }else{
            if ((passwordEle.value.length > 6) && (companyNameEle.value.length > 3)) {
                showModal();
            }
        }

    }, []);

    return (
        <FormWrapper
            hideModal={hideModal}
            showModal={showModal}
        />
    );
}

// Render the App component into the DOM
render(<App/>, document.getElementById('cs-modal-checkout-groups'));

