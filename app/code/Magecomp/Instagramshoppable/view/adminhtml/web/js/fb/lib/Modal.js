require(
    [
      'react',
      'reactDom'
    ],
    function (React,ReactDOM) {
      'use strict';

      var FBModal = React.createClass({
        displayName: 'Modal',

        render: function render() {
          return React.createElement(
              'div',
              {className: 'modal-container'},
              React.createElement(
                  'div',
                  {className: 'modal'},
                  React.createElement(
                      'div',
                      {className: 'modal-header'},
                      this.props.title
                  ),
                  React.createElement(
                      'div',
                      {className: 'modal-content'},
                      this.props.message
                  ),
                  React.createElement(
                      'div',
                      {className: 'modal-close'},
                      React.createElement(
                          'button',
                          {onClick: this.props.onClose, className: 'medium blue'},
                          'OK'
                      )
                  )
              )
          );
        }
      });
  });