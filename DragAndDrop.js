/***********************************************************
Component: DragAndDrop Feature
Developed by : Avinash (Devtrust.io)
Developed on : 29th Jan 2019
Modified on :
************************************************************/
import React, { Component } from 'react';
export default class DragAndDrop extends Component {
  constructor(props){
    super(props);
    this.state={
      onMouseoverIndex:"",  //NewIndex
      onDragOver: "", //Index of the element over which the picked-up element currently hovering
      onDragStart: "",
      tasks: [{id:'1', name:'John Doe', flag:'0'},
              {id:'2', name:'Mitchel Jhonson', flag:'0'},
              {id:'3', name:'Hannah Montannah', flag:'0'},
              {id:'4', name:'Avril Lavigne', flag:'1'},
              {id:'5', name:'Taylor Swift', flag:'1'}]
         }
  }
    
      //Function fires right after dragging starts
      onDragOver=(e)=>{
        console.log("onDragOver")
          e.preventDefault();
          if(e.target.value != 0){
            var value = e.target.value
            this.setState({ onDragOver : value  })
  
            if(value != this.state.onDragOver && value != undefined)
            // console.log("onDragOver :", e.target.value )
              this.setState({
                  onMouseoverIndex: value
                });

          } else if( this.state.tasks[e.target.value].flag == 0){
            this.setState({ onMouseoverIndex : this.state.onDragStart  })
          }
         
        }

      //this function gives the index of the picked-up element for dragging
      onDragStart=(ev, index)=>{
        console.log("onDragStart")
        //Index of the element picking up
         ev.dataTransfer.setData("id", index)
         var temp = ev.dataTransfer.getData("id")
          this.setState({ onDragStart: temp })
      }

      //this function fires after dropping of the element to the desired location
      onDrop=(e)=>{
        console.log("onDrop")
          const { tasks, onMouseoverIndex } = this.state;
          let id = e.dataTransfer.getData("id");
        
          //This function shift the elemnts to one index up or one index down; adjust the list after swaping
            function array_move(tasks, old_index, new_index) {
              tasks.splice(new_index, 0, tasks.splice(old_index, 1)[0]);
              return tasks; // for testing
            };
            array_move(tasks, id, onMouseoverIndex)
            this.setState({
              tasks: tasks,
            });
          
      }
 
  render () {

        const items = this.state.tasks.map((t, index)=>{
          //If flag is 0, the list item is draggable
          if(t.flag == 0){
          return <li
             id={t.name}
             value={index}
             key={t.name}
             onDragStart={e=> this.onDragStart(e, index)}
             draggable
             className="draggable"
             style={{backgroundColor: "pink", cursor:'pointer', border: '1px solid black', marginBottom:'2px'}}
             >
             {t.name}
           </li>
          }
          //the list item is not draggable
          else{
           return <li
           id={t.name}
           value={null}
           key={t.name}
           onDragStart={e=> this.onDragStart(e, index)}
           className="draggable"
           style={{backgroundColor: "grey", cursor: 'not-allowed', border: '1px solid black', marginBottom:'2px'}}
           >
           {t.name}
         </li>
          } 
         }); 
    return (
      <div className="container-drag">
        <ul className="droppable" style={{width:'300px'}} onDragOver={e=> this.onDragOver(e)} onDrop={e=> this.onDrop(e)}>
            {items}
        </ul>
      </div>
    );
  }
}