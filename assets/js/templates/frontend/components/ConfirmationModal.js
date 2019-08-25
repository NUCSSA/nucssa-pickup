import React from 'react';

export default function ConfirmationModal(props) {
  /**
   * props: {
   *  modalID - optional, default: 'confirmation-modal'
   *  title - optional, default: "Are You Sure?"
   *  body
   *  abortText - optional, default: "Cancel"
   *  confirmButtonClass - optional, no default value.
   *  confirmText - optional, default: "Confirm"
   *  confirmAction
   * }
   */

  return (
    <div className="modal" id={props.modalID || 'confirmation-modal'}>
      <div className="modal-content">
        <h4>{props.title || "Are You Sure?"}</h4>
        <div className="modal-body">
          { props.body }
        </div>
      </div>
      <div className="modal-footer">
        <button type="button" className="modal-close btn-flat grey white-text">
          {props.abortText || 'Cancel'}
        </button>

        <button type="button"
          className={`modal-close btn-flat red darken-4 white-text ${props.confirmButtonClass}`}
          onClick={props.confirmAction}
          style={{marginLeft: '1rem'}}
        >
          {props.confirmText || 'Confirm'}
        </button>

      </div>
    </div>
  );
}
