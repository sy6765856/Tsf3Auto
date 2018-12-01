package gen

import (
	"io/ioutil"
	"encoding/json"
	"fmt"
	"os"
	"github.com/tallstoat/pbparser"
	"strings"
	"path"
	"text/template"
)

const GENERATE_SUFFIX = ".php"
const TPL_SUFFIX = ".tpl"

type cmd struct {
	Cmd  int32
	Name string
}

type Gen struct {
	TemplatePath string
	Dirs         [] string
	Name         string
	Port         int
	Proto        string
	Cmds         [] cmd
	Time         string
}

func Run(name string, pbName string) {
	genObj := Gen{Name: name, Proto:pbName, Port:9700}
	genObj.loadConfig()
	genObj.readPb(pbName)
	fmt.Printf("genObj init: %+v", genObj)
	genObj.genDirs()
	genObj.genFiles()
}

func (genObj *Gen) readPb(protoName string) {
	pf, err := pbparser.ParseFile(protoName)
	if err != nil {
		fmt.Printf("Unable to parse proto file: %v \n", err)
		os.Exit(-1)
	}
	var cmds []cmd
	for k := range pf.Enums[0].EnumConstants {
		v := pf.Enums[0].EnumConstants[k]
		//fmt.Printf("%v %v\n", v.Name, v.Tag)
		item := cmd{int32(v.Tag), v.Name}
		cmds = append(cmds, item)
	}
	//fmt.Printf("%v", cmds)
	genObj.Cmds = cmds
}

func (genObj *Gen) loadConfig() {
	filename := "../config/config.json"
	data, err := ioutil.ReadFile(filename)
	//fmt.Printf("config data:%v", data)
	if err != nil {
		return
	}
	err = json.Unmarshal(data, genObj)
	if err != nil {
		return
	}
}

func (genObj *Gen) genDirs() {
	basePath := genObj.Name
	os.Mkdir(basePath, os.ModePerm)
	for _,dir:=range genObj.Dirs {
		os.Mkdir(basePath+"/"+dir, os.ModePerm)
	}
}

func (genObj *Gen) genFiles() {
	files, _ := ioutil.ReadDir(genObj.TemplatePath)
	for _, f := range files {
		t, _ := template.ParseFiles(genObj.TemplatePath + "/" + f.Name())
		filePaths := strings.Split(f.Name(), "_")
		outPath := ""
		for item := range filePaths {
			outPath = outPath + "/" + filePaths[item]
		}
		if (TPL_SUFFIX != path.Ext(outPath)) {
			continue
		}
		outPath = genObj.Name + outPath
		fileSuffix := path.Ext(outPath)
		outPath = strings.TrimSuffix(outPath, fileSuffix)
		//fmt.Printf("%v\n", out_path)
		outputFile, _ := os.OpenFile(outPath+GENERATE_SUFFIX, os.O_WRONLY|os.O_CREATE, 0666)
		t.Execute(outputFile, genObj)
	}
}


