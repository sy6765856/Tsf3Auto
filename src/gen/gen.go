package gen

import (
	"io/ioutil"
	"encoding/json"
	"fmt"
	"os"
	"github.com/tallstoat/pbparser"
	"text/template"
	"time"
)

type cmd struct {
	Cmd  int32
	Name string
}

type Gen struct {
	Name                string             //生成服务名
	Port                string             //服务端口
	TemplatePath        string             //生成模板路径
	TemplateSuffix      string             //模板后缀
	GeneratedFileSuffix string             //生成文件后缀
	Dirs                [] string          //生成目录(config.json)
	ReqBodyName         string             //服务请求体在proto文件中名称
	RsqBodyName         string             //服务响应体在proto文件中名称
	ProtoFile           pbparser.ProtoFile //pb结构体，通过pb生成服务
	Cmds                [] cmd
	Time                string
}

func Run(protoPath string, name string, port string) {
	genObj := Gen{Name: name, Port: port}
	genObj.loadConfig().loadPbContent(protoPath).init().genFalseWork(genObj.TemplatePath,"")
	//fmt.Printf("genObj init: %+v", genObj)
}

func (genObj *Gen) loadConfig() *Gen {
	filename := "../config/config.json"
	data, err := ioutil.ReadFile(filename)
	//fmt.Printf("config data:%v", data)
	if err != nil {
		return nil
	}
	err = json.Unmarshal(data, genObj)
	if err != nil {
		return nil
	}
	return genObj
}

func (genObj *Gen) loadPbContent(protoName string) *Gen {
	pf, err := pbparser.ParseFile(protoName)
	if err != nil {
		fmt.Printf("Unable to parse proto file: %v \n", err)
		os.Exit(-1)
	}
	genObj.ProtoFile = pf
	var cmds []cmd
	for k := range pf.Enums[0].EnumConstants {
		v := pf.Enums[0].EnumConstants[k]
		//fmt.Printf("%v %v\n", v.Name, v.Tag)
		item := cmd{int32(v.Tag), v.Name}
		cmds = append(cmds, item)
	}
	//fmt.Printf("%v", cmds)
	genObj.Cmds = cmds
	return genObj
}

func (genObj *Gen) init() *Gen {
	genObj.Time = time.Now().String()
	return genObj
}

func (genObj *Gen) genFalseWork(basePath string, dirPath string) *Gen {
	files, _ := ioutil.ReadDir(basePath+dirPath)
	os.Mkdir(genObj.Name + dirPath, os.ModePerm)
	for _, f := range files {
		if(f.IsDir()) {
			genObj.genFalseWork(basePath, dirPath+"/"+f.Name())
		} else {
			t, _ := template.ParseFiles(basePath+dirPath + "/" + f.Name())
			outPath := genObj.Name + dirPath + "/" + f.Name()
			//fmt.Printf("%v\n", outPath)
			outputFile, _ := os.OpenFile(outPath, os.O_WRONLY|os.O_CREATE, 0666)
			t.Execute(outputFile, genObj)
			outputFile.Close()
		}
	}
	return genObj
}

//func (genObj *Gen) genDirs() *Gen {
//	basePath := genObj.Name
//	os.Mkdir(basePath, os.ModePerm)
//	for _, dir := range genObj.Dirs {
//		os.Mkdir(basePath+"/"+dir, os.ModePerm)
//	}
//	return genObj
//}
//
//func (genObj *Gen) genFiles() *Gen {
//	files, _ := ioutil.ReadDir(genObj.TemplatePath)
//	for _, f := range files {
//		t, _ := template.ParseFiles(genObj.TemplatePath + "/" + f.Name())
//		filePaths := strings.Split(f.Name(), "_")
//		outPath := ""
//		for item := range filePaths {
//			outPath = outPath + "/" + filePaths[item]
//		}
//		if (genObj.TemplateSuffix != path.Ext(outPath)) {
//			continue
//		}
//		outPath = genObj.Name + outPath
//		fileSuffix := path.Ext(outPath)
//		outPath = strings.TrimSuffix(outPath, fileSuffix)
//		//fmt.Printf("%v\n", out_path)
//		outputFile, _ := os.OpenFile(outPath+genObj.GeneratedFileSuffix, os.O_WRONLY|os.O_CREATE, 0666)
//		t.Execute(outputFile, genObj)
//	}
//	return genObj
//}
