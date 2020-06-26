#!/usr/bin/env python3
import sys
import json


def main(path):
    with open(path) as fin:
        data = json.load(fin)
        for block_name, block_items in data.items():
            print("[{}]".format(block_name))
            for k, v in block_items.items():
                vstr = ','.join(list(map(str, v))) if isinstance(v, list) else v
                print("{} = {}".format(k, vstr))
            print()


if __name__ == "__main__":
    main(sys.argv[1])
