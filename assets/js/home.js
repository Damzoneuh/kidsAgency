import React, {Component} from 'react';
import ReactDOM from 'react-dom';
import 'bootstrap/dist/css/bootstrap.css';
import '../sass/global.scss';

export default class Home extends Component{
    constructor(props){
        super(props);
    }

    render() {
        return(
            <div className="container"><div className="col-6"> Hello world</div></div>
        )
    }
}

ReactDOM.render(<Home />, document.getElementById('home'));

