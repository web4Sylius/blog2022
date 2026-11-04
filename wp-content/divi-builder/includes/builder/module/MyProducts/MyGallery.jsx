import React, { Component, Fragment } from 'react';
	
import './products.css';
	
class MyProducts extends Component {
	
  static slug = 'my_products';

  state = {
    photos: []
  }
  	
  render() {
    return (
     <Fragment>
       <h1 className="my-products-heading">{this.props.heading}</h1>
       <div className="my-products-footnote">{this.props.content()}</div>
     </Fragment>
    );
  }
}
          	
export default MyProducts;