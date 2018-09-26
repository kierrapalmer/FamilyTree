//Note: numChildren includes spouses
let tree =[
    {
        name: "Parent",
        spouse: null,
        generation: 0,
        marriageUnionId: 0,
        parentMarriageUnionId: null,
        numberChildren: 3,
        x: null,
        y: null,
        width: null,

    },
    {
        name: "Child1",
        spouse: "ChildSpouse1",
        generation: 1,
        marriageUnionId: 1,
        parentMarriageUnionId: 0,
        numberChildren: 2,
        x: null,
        y: null,
        width: null,
    },
    {
        name: "Child2",
        spouse: null,
        generation: 1,
        marriageUnionId: 2,
        parentMarriageUnionId: 0,
        numberChildren: 0,
        x: null,
        y: null,
        width: null,
    },
    {
        name: "Child3",
        spouse: null,
        generation: 1,
        marriageUnionId: null,
        parentMarriageUnionId: 0,
        numberChildren: 0,
        x: null,
        y: null,
        width: null,
    },
    {
        name: "Grandchild1A",
        spouse: "Grandchild1A Spouse",
        generation: 2,
        marriageUnionId: 3,
        parentMarriageUnionId: 1,
        numberChildren: 0,
        x: null,
        y: null,
        width: null,
    },
    {
        name: "Grandchild1B",
        spouse: null,
        generation: 2,
        marriageUnionId: null,
        parentMarriageUnionId: 1,
        numberChildren: 0,
        x: null,
        y: null,
        width: null,
    },
    {
        name: "Grandchild2A",
        spouse: null,
        generation: 2,
        marriageUnionId: null,
        parentMarriageUnionId: 2,
        numberChildren: 0,
        x: null,
        y: null,
        width: null,
    },

];


let screenWidth = screen.width;
let height = screen.height;
let maxX = screenWidth + 20;
let maxY = height + 10;
let minX = 20;
let minY = 10;

let x = maxX - maxX/2;
let y = minY;
let horizontalOffset = 120;

let treeList = document.getElementById("tree");
for(let n = 0; n < 5; n++){

    createNode(n);
    let childWidth = 0;
    if(n >= 1){
        /*if(tree[n-1].generation != tree[n].generation){
            y+=50;
            x=horizontalOffset*1.5;
        }

        if(tree[n].isSpouse == 1)
            x += 60;
        else if(tree[n].numberChildren > 0)
            x+= tree[n].numberChildren * horizontalOffset/2;
        else
            x+= horizontalOffset*1.5;*/


        let numOfSiblings = tree[n-1].numberChildren;      //number of parents children
        if(numOfSiblings > 0){
            childWidth = screenWidth/numOfSiblings;

            if(tree[n-1].generation !== tree[n].generation){
                y+=50;
                x = childWidth /2;
            }
            if(tree[n].marriageUnionId != null && !tree[n].isSpouse)
                x-=10;
            else if(tree[n].marriageUnionId != null && tree[n].isSpouse)
                x+=20;
            tree[n].width = childWidth;

        }

    }



    tree[n].x = x;
    tree[n].y = y;

    $("#child"+n).offset({top: y, left: x});




}

function createNode(x){
    let li = document.createElement("li");
    li.appendChild(document.createTextNode(tree[x].name));
    li.setAttribute("id", "child"+x);
    treeList.appendChild(li);
}
