#!/usr/bin/env bash 

# -------------------- Copyright --------------------
# FileName: install.sh
# Description: Git clone and install Xcode MarkdownOfflineReading from https://github.com/AndyM129/MarkdownOfflineReading
# Version: 1.0
# Date: 2020/12/22
# Author: Andy Meng
# Email: andy_m129@163.com
# -------------------- History --------------------
# 2020/12/22: v1.0
# -------------------- End --------------------


help() {
    echo "usage:"
    echo -e "\tbash $0 [-dehv]"
    echo "params:"
    echo -e "\t-d:\tEnabled debug mode."
    echo -e "\t-e:\tEnabled edit mode."
    echo -e "\t-h:\tShow help."
    echo -e "\t-v:\tEnabled verbose mode."
}


process() {
    # 脚本的安装路径
    install_path="$HOME/.bash_files/MarkdownOfflineReading"
    echo "准备为您安装 MarkdownOfflineReading 工具，路径为：${install_path}"

    # 若目录已存在，则先备份
    if [ -d $install_path ];then

        # 当前时间
        current_datetime="`date +%Y%m%d%H%M%s`"

        # 备份路径
        bakpath="${install_path}.bak${current_datetime}"
        cp -r "$install_path" "$bakpath"
        echo "文件夹存在，已为您进行备份：${bakpath}"
    else 
        mkdir "$install_path"
    fi

    echo "开始拉取最新的 MarkdownOfflineReading ..."
    rm -rf "$install_path"
    git clone https://github.com/AndyM129/MarkdownOfflineReading.git $install_path
    echo "已成功拉取最新的 MarkdownOfflineReading。\n\n"
    
    exit 0;
}


main() {
    debug=false
    edit=false
    verbose=false

    while getopts "dehv" OPT; do
        case $OPT in
            d)
                debug=true
                ;;
            e)
                edit=true
                ;;
            v)
                verbose=true
                ;;
            
            h)
                help
                exit 0
                ;;
            ?)
                help
                exit 1
                ;;
        esac
    done

    if $debug; then
        echo "----- variables -----"
        echo -e "debug: $debug"
        echo -e "edit: $edit"
        echo -e "verbose: $verbose"
        echo
    fi

    process
}

main $@
