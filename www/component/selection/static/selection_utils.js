/* All the main selection methods */		findIndexInConfig = function(config, name){		var index = null;		for(var i = 0; i < config.length; i++){			if(config[i].name == name){				index = i;				break;			}		}		return index;	}		getConfigCategoryIndexes = function(){		var categories = [];		categories[0] = {index:1,name:"Information Session"};		categories[1] = {index:2,name:"Exam Subject"};		categories[2] = {index:3,name:"Applicant"};		return categories;	}		getStepValue = function(steps,name){		for(var i = 0; i < steps.length; i++){			if(steps[i].name == name)				return steps[i].value;		}	}