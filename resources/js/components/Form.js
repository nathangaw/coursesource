const csForm = {

    formState: {},

    formErrors: {},

    /**
     *
     * @param value
     * @returns {boolean}
     */
    notEmpty: function (value) {
        return value.length > 0;
    },


    /**
     *
     * @param email
     * @returns {boolean}
     */
    validEmail: function (value) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(value);
    },


    /**
     *
     * @param value
     * @param length
     * @returns {boolean}
     */
    minLength: function (value, length) {
        return value.length >= length;
    },

    /**
     *
     * @param value
     * @param length
     * @returns {boolean}
     */
    maxLength: function (value, length) {
        return value.length <= length;
    },


    /**
     * @param group_name
     * @returns {Promise<boolean>}
     */
    groupNotExists: async function (group_name) {
        let result = await this.groupExists( group_name );
        return !result.result;
    },

    /**
     *
     * @param group_name
     * @returns {Promise<*>}
     */
    groupExists: async function (group_name) {
        let result = await jQuery.ajax({
            url: Coursesource.ajaxurl,
            method: 'post',
            dataType: 'json',
            data: {
                action: 'group_exists',
                nonce: Coursesource.nonce,
                group_name: group_name
            }
        }).done(function (response) {
            return response;
        });
        return result;
    },


    formErrorClass: function (fieldName, formErrors) {
        let className = '';
        if ((formErrors[fieldName] === true) || formErrors[fieldName].error) {
            className = `form-error form-error__${fieldName}`;
        }
        return className;
    },


    /**
     *
     * @returns {boolean}
     */
    formHasErrors: function (formState, formErrors) {
        setFormErrors(this.validateForm(formState, formErrors));
        let formErrorValues = Object.values(formErrors);
        return formErrorValues.some(formError => formError.error === true);
    },


    /**
     *
     * @param formState
     * @param formErrors
     * @returns {Promise<*>}
     */
    validateForm: async function (formState, formErrors) {
        let newFormErrors = {...formErrors};
        // Check for any empty or too short field values
        for (const [key, value] of Object.entries(formState)) {
            if (formErrors.hasOwnProperty(key)) {
                let validators = formErrors[key].validators;
                validators.every((validator) => {
                    let validationResult = false;
                    let params = validator.params ?? false;
                    if (params) {
                        validationResult = csForm[validator.name](value, ...params);
                    } else {
                        validationResult = csForm[validator.name](value);
                    }
                    newFormErrors[key].error = !validationResult;
                    newFormErrors[key].message = (validationResult) ? '' : validator.message;
                    return validationResult;
                });
            }
        }
        return newFormErrors;
    },

};

export default csForm;