import React from "react";
const { __ } = wp.i18n;
const { registerBlockType } = wp.blocks;
const { TextControl } = wp.components;
const { useState } = wp.element;

registerBlockType("ninja-tables/guten-block", {
  title: __("Ninja Tables"),
  icon: (
    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 321.98 249.25">
      <path
        class="A"
        d="M312.48 249.25H9.5a9.51 9.51 0 0 1-9.5-9.5V9.5A9.51 9.51 0 0 1 9.5 0h303a9.51 9.51 0 0 1 9.5 9.5v230.25a9.51 9.51 0 0 1-9.52 9.5zM9.5 7A2.53 2.53 0 0 0 7 9.5v230.25a2.53 2.53 0 0 0 2.5 2.5h303a2.53 2.53 0 0 0 2.5-2.5V9.5a2.53 2.53 0 0 0-2.5-2.5z"
      />
      <path class="A" d="M75 44.37h8.75v202.7H75z" />
      <path class="B" d="M129.37 44.37" />
      <path class="C" d="M249.37 44.37" />
      <path
        class="A"
        d="M6.16.5h309.66a6 6 0 0 1 6 6v43.8a.63.63 0 0 1-.63.63H.8a.63.63 0 0 1-.63-.63V6.5a6 6 0 0 1 6-6zM4.88 142.84h312.6v15.1H4.88zM22.47 90h28.27v16.97H22.47zm89.13 0h165.67v16.97H111.6zM22.47 190h28.27v16.97H22.47zm89.13 0h165.67v16.97H111.6z"
      />
    </svg>
  ),
  category: "formatting",
  keywords: [
    __("Ninja Tables"),
    __("Gutenberg Block"),
    __("ninja-tables-gutenberg-block"),
  ],
  attributes: {
    tableId: {
      type: "string",
    },
    dataSource: {
      type: "string",
    },
  },
  edit({ attributes, setAttributes }) {
    const [options, setOptions] = useState([]);
    const config = window.ninja_tables_tiny_mce;

    const changeEventHandler = (event) => {
      const data = event.target.value.split(",");
      const tableId = data[0];
      const dataSource = data[1];
      setAttributes({ tableId });
      setAttributes({ dataSource });
      setOptions([]);
    };

    const getTableNameById = (id) => {
      if (id) {
        const table = config.tables.find(
          (table) => parseInt(table.value) === parseInt(id)
        );
        return table.text;
      }
    };

    const searchTable = (event) => {
      if (typeof event === "string") {
        const search = event;
        const tables = config.tables.filter((table) =>
          table.text.toLowerCase().includes(search.toLowerCase())
        );
        const options = tables.map((table) => ({
          value: [table.value, table.data_source],
          label: table.text,
        }));

        setOptions(options);

        if (options.length) {
          setAttributes({ tableId: "" });
        }
      } else {
        const options = config.tables.map((table) => ({
          value: [table.value, table.data_source],
          label: table.text,
        }));

        setOptions(options);
      }
    };

    return (
      <div className="ninja-tables-guten-wrapper">
        <div className="ninja-tables-logo">
          <img src={config.logo} alt="ninja-tables-logo" />
        </div>

        <div className="nt-guten-block-select">
          <TextControl
            value={getTableNameById(attributes.tableId)}
            placeholder={__("Search a Table")}
            onClick={searchTable}
            onChange={searchTable}
          />
          {options.length > 0 && (
            <ul>
              {options.map((option) => (
                <li key={option.value}>
                  <button onClick={changeEventHandler} value={option.value}>
                    {option.label}
                  </button>
                </li>
              ))}
            </ul>
          )}
        </div>
      </div>
    );
  },
  save({ attributes }) {
    if (attributes.dataSource === "drag_and_drop") {
      return '[ninja_table_builder id="' + attributes.tableId + '"]';
    } else {
      return '[ninja_tables id="' + attributes.tableId + '"]';
    }
  },
});
