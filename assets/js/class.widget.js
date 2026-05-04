class WidgetCatProblemWidget extends CWidget {

    getUpdateRequestData() {
        const data = super.getUpdateRequestData();
        if (!data.fields) {
            data.fields = {};
        }
        data.fields.cb = localStorage.getItem('cat_last_click') || '0';
        return data;
    }

    onActivate() {
        this._click_handler = (e) => {
            if (e.target.closest('.cat-problem-container img')) {
                localStorage.setItem('cat_last_click', Date.now());
                this._startUpdating();
            }
        };
        this._target.addEventListener('click', this._click_handler);

        this._acknowledge_handler = () => {
            setTimeout(() => this._startUpdating(), 500);
        };
        $.subscribe('acknowledge.create', this._acknowledge_handler);
    }

    onDeactivate() {
        this._target.removeEventListener('click', this._click_handler);
        $.unsubscribe('acknowledge.create', this._acknowledge_handler);
    }
}
