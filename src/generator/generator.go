package generator

import (
	"os"
	"io/ioutil"
	"strings"
	"path"
	"text/template"
	"time"
	"github.com/tallstoat/pbparser"
	"fmt"
)

const TPL_PATH = "../templates"
const GENERATE_SUFFIX = ".php"
const TPL_SUFFIX = ".tpl"

type cmd struct {
	Cmd  int32
	Name string
}
type tsfSvr struct {
	Name string
	Port int
	Time string
	Cmds [] cmd
}
func Auto(name string, port int, protoName string) {
	dir := name + "_mix_srv"
	createSvrFolders(dir)
	createFiles(dir, port, protoName)
}
func createSvrFolders(dir string) {
	os.Mkdir(dir, os.ModePerm)
	os.Mkdir(dir+"/"+"Mix", os.ModePerm)
	os.Mkdir(dir+"/"+"Mix/Base", os.ModePerm)
	os.Mkdir(dir+"/"+"Mix/Component", os.ModePerm)
	os.Mkdir(dir+"/"+"Mix/Controller", os.ModePerm)
	os.Mkdir(dir+"/"+"Mix/Model", os.ModePerm)
	os.Mkdir(dir+"/"+"Command", os.ModePerm)
	os.Mkdir(dir+"/"+"Config", os.ModePerm)
	os.Mkdir(dir+"/"+"Config/env", os.ModePerm)
	os.Mkdir(dir+"/"+"Config/env/dev", os.ModePerm)
	os.Mkdir(dir+"/"+"Config/env/oa", os.ModePerm)
	os.Mkdir(dir+"/"+"Config/env/ol", os.ModePerm)
	os.Mkdir(dir+"/"+"library", os.ModePerm)
	os.Mkdir(dir+"/"+"UserWorker", os.ModePerm)
}

func createFiles(svrName string, port int, protoName string) {
	data := tsfSvr{
		Name: svrName,
		Port: port,
		Time: time.Now().String(),
	}
	data.Cmds = readPb(protoName)
	//fmt.Printf("%v", data.Cmds)
	files, _ := ioutil.ReadDir(TPL_PATH)
	for _, f := range files {
		t, _ := template.ParseFiles(TPL_PATH + "/" + f.Name())
		filePaths := strings.Split(f.Name(), "_")
		outPath := ""
		for item := range filePaths {
			outPath = outPath + "/" + filePaths[item]
		}
		if (TPL_SUFFIX != path.Ext(outPath)) {
			continue
		}
		outPath = svrName + outPath
		fileSuffix := path.Ext(outPath)
		outPath = strings.TrimSuffix(outPath, fileSuffix)
		//fmt.Printf("%v\n", out_path)
		outputFile, _ := os.OpenFile(outPath+GENERATE_SUFFIX, os.O_WRONLY|os.O_CREATE, 0666)
		t.Execute(outputFile, data)
	}
}

func readPb(protoName string) []cmd {
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
	fmt.Printf("%v", cmds)
	return cmds
}

