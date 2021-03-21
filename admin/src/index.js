import MyTemplate from "./hello.marko";

MyTemplate.renderSync({ name: "Marko" }).appendTo(document.getElementById('wordland-editting-infos-box'));