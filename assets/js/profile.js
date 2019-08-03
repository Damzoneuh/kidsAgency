import React, {Component} from 'react';
import ReactDOM from 'react-dom';

export default class Profile extends Component{
    constructor(props){
        super(props)
    }

    render(){
        return(
            <div className="container-fluid">
                <div className="row">
                    <div className="col-lg-6 col-12" >
                        <div className="form-group">
                            <label >nom </label>
                            <input type="text" name="name" className="form-control"/>
                        </div>
                    </div>
                </div>
            </div>
        )
    }
}
ReactDOM.render(<Profile/>, document.getElementById('profile'));