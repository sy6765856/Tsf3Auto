package main

import (
	"log"
	"os"
	"github.com/urfave/cli"
	"./gen"
)

func main() {
	app := cli.NewApp()
	app.Name = "TSF3 Generator"
	app.Usage = "Generate some common application template classes from proto"
	app.Commands = []cli.Command{
		{
			Name:    "all",
			Aliases: []string{"a"},
			Usage:   "generate falseWork from proto",
			Action: func(c *cli.Context) error {
				///todo check params
				protoPath := c.Args().Get(0)
				srvName := c.Args().Get(1)
				port := c.Args().Get(2)
				gen.Run(protoPath, srvName, port)
				return nil
			},
		},
	}
	app.Version = "0.0.1"
	err := app.Run(os.Args)
	if err != nil {
		log.Fatal(err)
	}
}




